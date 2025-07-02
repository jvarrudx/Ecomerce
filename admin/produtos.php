<?php
require_once(dirname(__DIR__) . '/includes/config.php');
require_once(dirname(__DIR__) . '/includes/db.php');
require_once(BASE_PATH . '/includes/functions.php');

// Acesso permitido apenas para Vendedores e Admins
if (!ehVendedor()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Lógica para DESATIVAR um produto (o antigo "Excluir")
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $id_para_desativar = intval($_POST['delete_id']);
    $stmt = $conn->prepare("UPDATE produtos SET status = 'inativo' WHERE id = ?");
    $stmt->bind_param("i", $id_para_desativar);
    if ($stmt->execute()) {
        $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Produto desativado com sucesso!'];
    } else {
        $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao desativar o produto.'];
    }
    $stmt->close();
    header('Location: ' . BASE_URL . '/admin/produtos.php');
    exit;
}

// Lógica para REATIVAR um produto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reactivate_id'])) {
    $id_para_reativar = intval($_POST['reactivate_id']);
    $stmt = $conn->prepare("UPDATE produtos SET status = 'ativo' WHERE id = ?");
    $stmt->bind_param("i", $id_para_reativar);
    if ($stmt->execute()) {
        $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Produto reativado com sucesso!'];
    } else {
        $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao reativar o produto.'];
    }
    $stmt->close();
    header('Location: ' . BASE_URL . '/admin/produtos.php');
    exit;
}

// LÓGICA PARA ADICIONAR UM NOVO PRODUTO (RESTAURADA)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    if (empty($_POST['nome']) || empty($_POST['descricao']) || !isset($_POST['preco']) || !isset($_POST['estoque'])) {
        $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Todos os campos são obrigatórios.'];
    } else {
        $nome = $_POST['nome'];
        $descricao = $_POST['descricao'];
        $preco = floatval($_POST['preco']);
        $estoque = intval($_POST['estoque']);
        $vendedor_id = $_SESSION['usuario_id'];
        $caminho_imagem = '';

        // Lógica de Upload da Imagem
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

        // Insere o novo produto com status 'ativo' por padrão
        $stmt = $conn->prepare("INSERT INTO produtos (nome, descricao, preco, estoque, vendedor_id, status, imagem) VALUES (?, ?, ?, ?, ?, 'ativo', ?)");
        $stmt->bind_param("ssdiis", $nome, $descricao, $preco, $estoque, $vendedor_id, $caminho_imagem);

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Produto adicionado com sucesso!'];
        } else {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao adicionar o produto.'];
            if(!empty($caminho_imagem) && file_exists(BASE_PATH . '/' . $caminho_imagem)) {
                unlink(BASE_PATH . '/' . $caminho_imagem);
            }
        }
        $stmt->close();
    }
    header('Location: ' . BASE_URL . '/admin/produtos.php');
    exit;
}

require_once(BASE_PATH . '/includes/header.php');
?>

<h1>Gerenciar Produtos</h1>

<?php
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
                <tr>
                    <th>Imagem</th><th>Produto</th><th>Preço</th><th>Estoque</th><th>Vendedor</th><th>Status</th><th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT p.*, u.nome AS nome_vendedor 
                        FROM produtos p 
                        LEFT JOIN usuarios u ON p.vendedor_id = u.id 
                        ORDER BY FIELD(p.status, 'ativo', 'inativo'), p.id DESC";
                $res = $conn->query($sql);
                while ($p = $res->fetch_assoc()) :
                    $classe_inativo = ($p['status'] === 'inativo') ? 'produto-inativo' : '';
                ?>
                    <tr class="<?= $classe_inativo ?>">
                        <td>
                            <?php if(!empty($p['imagem'])): ?><img src="<?= BASE_URL . '/' . htmlspecialchars($p['imagem']) ?>" alt="<?= htmlspecialchars($p['nome']) ?>" width="50"><?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['nome']) ?></td>
                        <td>R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
                        <td><?= $p['estoque'] ?></td>
                        <td><?= htmlspecialchars($p['nome_vendedor'] ?? 'Sistema') ?></td>
                        <td>
                            <?php if ($p['status'] === 'ativo'): ?><span class="badge bg-success">Ativo</span><?php else: ?><span class="badge bg-secondary">Inativo</span><?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($p['status'] === 'ativo'): ?>
                                <form method="post" action="<?= BASE_URL ?>/admin/produtos.php" onsubmit="return confirm('Tem certeza que deseja desativar este produto?');" style="display: inline;">
                                    <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Desativar</button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="<?= BASE_URL ?>/admin/produtos.php" style="display: inline;">
                                    <input type="hidden" name="reactivate_id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-info">Reativar</button>
                                </form>
                            <?php endif; ?>
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