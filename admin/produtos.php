<?php
// 1. Configuração e Segurança Inicial
require_once(dirname(__DIR__) . '/includes/config.php');
require_once(dirname(__DIR__) . '/includes/db.php');
require_once(BASE_PATH . '/includes/functions.php');

// Verificação de permissão: SÓ PERMITE ACESSO PARA VENDEDORES E ADMINS
if (!ehVendedor()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// --- LÓGICA DE PROCESSAMENTO DOS FORMULÁRIOS ---

// Lógica para DELETAR um produto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $id_para_deletar = intval($_POST['delete_id']);
    
    // (Opcional) Futuramente, poderíamos adicionar uma regra aqui para que um vendedor
    // só possa deletar os próprios produtos. Por enquanto, a lógica está aberta.
    
    $stmt_img = $conn->prepare("SELECT imagem FROM produtos WHERE id = ?");
    $stmt_img->bind_param("i", $id_para_deletar);
    $stmt_img->execute();
    $resultado_img = $stmt_img->get_result();
    if ($produto = $resultado_img->fetch_assoc()) {
        if (!empty($produto['imagem']) && file_exists(BASE_PATH . '/' . $produto['imagem'])) {
            unlink(BASE_PATH . '/' . $produto['imagem']);
        }
    }
    $stmt_img->close();

    $stmt = $conn->prepare("DELETE FROM produtos WHERE id = ?");
    $stmt->bind_param("i", $id_para_deletar);
    if ($stmt->execute()) {
        $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Produto excluído com sucesso!'];
    } else {
        $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao excluir o produto.'];
    }
    $stmt->close();
    
    header('Location: ' . BASE_URL . '/admin/produtos.php');
    exit;
}

// Lógica para ADICIONAR um novo produto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    if (empty($_POST['nome']) || empty($_POST['descricao']) || !isset($_POST['preco']) || !isset($_POST['estoque'])) {
        $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Todos os campos são obrigatórios.'];
    } else {
        $nome = $_POST['nome'];
        $descricao = $_POST['descricao'];
        $preco = floatval($_POST['preco']);
        $estoque = intval($_POST['estoque']);
        $vendedor_id = $_SESSION['usuario_id']; // Associa o produto ao usuário logado
        $caminho_imagem = '';

        // Lógica de Upload da Imagem...
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
            $upload_dir = BASE_PATH . '/uploads/';
            $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
            $nome_arquivo_unico = uniqid('produto_', true) . '.' . $extensao;
            $caminho_destino = $upload_dir . $nome_arquivo_unico;
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($extensao, $extensoes_permitidas) && $_FILES['imagem']['size'] < 2000000) {
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho_destino)) {
                    $caminho_imagem = 'uploads/' . $nome_arquivo_unico;
                }
            }
        }

        $stmt = $conn->prepare("INSERT INTO produtos (nome, descricao, preco, estoque, vendedor_id, imagem) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiis", $nome, $descricao, $preco, $estoque, $vendedor_id, $caminho_imagem);

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Produto adicionado com sucesso!'];
        } else {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao adicionar o produto.'];
        }
        $stmt->close();
    }
    
    header('Location: ' . BASE_URL . '/admin/produtos.php');
    exit;
}

// Inclui o header APÓS toda a lógica de processamento
require_once(BASE_PATH . '/includes/header.php');
?>

<h1>Gerenciar Produtos</h1>

<?php
// Exibe mensagens de feedback (sucesso, erro)
if (isset($_SESSION['mensagem'])) {
    echo '<div class="alert alert-' . $_SESSION['mensagem']['tipo'] . '">' . htmlspecialchars($_SESSION['mensagem']['texto']) . '</div>';
    unset($_SESSION['mensagem']);
}
?>

<div class="card bg-light mb-4">
    <div class="card-header"><h5 class="mb-0">Adicionar Novo Produto</h5></div>
    <div class="card-body">
        <form method="post" action="<?= BASE_URL ?>/admin/produtos.php" enctype="multipart/form-data">
            <div class="mb-3"><label for="nome" class="form-label">Nome</label><input type="text" id="nome" name="nome" class="form-control" required></div>
            <div class="mb-3"><label for="descricao" class="form-label">Descrição</label><textarea id="descricao" name="descricao" class="form-control" rows="3" required></textarea></div>
            <div class="row">
                <div class="col-md-6 mb-3"><label for="preco" class="form-label">Preço (ex: 99.90)</label><input type="number" step="0.01" id="preco" name="preco" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label for="estoque" class="form-label">Quantidade em Estoque</label><input type="number" id="estoque" name="estoque" class="form-control" required></div>
            </div>
            <div class="mb-3"><label for="imagem" class="form-label">Imagem do Produto</label><input type="file" id="imagem" name="imagem" class="form-control"></div>
            <button class="btn btn-success" type="submit" name="add_product">Adicionar Produto</button>
        </form>
    </div>
</div>

<hr>

<div class="card bg-light">
    <div class="card-header"><h5 class="mb-0">Produtos Atuais</h5></div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Imagem</th><th>Produto</th><th>Preço</th><th>Estoque</th><th>Vendedor</th><th class="text-end">Ações</th></tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT p.*, u.nome AS nome_vendedor FROM produtos p LEFT JOIN usuarios u ON p.vendedor_id = u.id ORDER BY p.id DESC";
                $res = $conn->query($sql);
                while ($p = $res->fetch_assoc()) :
                ?>
                    <tr>
                        <td>
                            <?php if(!empty($p['imagem'])): ?><img src="<?= BASE_URL . '/' . htmlspecialchars($p['imagem']) ?>" alt="<?= htmlspecialchars($p['nome']) ?>" width="50"><?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['nome']) ?></td>
                        <td>R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
                        <td><?= $p['estoque'] ?></td>
                        <td><?= htmlspecialchars($p['nome_vendedor'] ?? 'Sistema') ?></td>
                        <td class="text-end">
                            <form method="post" action="<?= BASE_URL ?>/admin/produtos.php" onsubmit="return confirm('Tem certeza que deseja excluir este produto?');" style="display: inline;">
                                <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once(BASE_PATH . '/includes/footer.php');
?>