<?php
// 1. Configuração e Segurança Inicial
require_once(dirname(__DIR__) . '/includes/config.php');
require_once(dirname(__DIR__) . '/includes/db.php');
require_once(BASE_PATH . '/includes/functions.php');

if (!ehAdmin()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// --- LÓGICA DE PROCESSAMENTO DO FORMULÁRIO ---

// Lógica para DELETAR um produto e sua imagem
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $id_para_deletar = intval($_POST['delete_id']);
    
    // Primeiro, busca o caminho da imagem para poder apagá-la
    $stmt_img = $conn->prepare("SELECT imagem FROM produtos WHERE id = ?");
    $stmt_img->bind_param("i", $id_para_deletar);
    $stmt_img->execute();
    $resultado_img = $stmt_img->get_result();
    if ($produto = $resultado_img->fetch_assoc()) {
        if (!empty($produto['imagem']) && file_exists(BASE_PATH . '/' . $produto['imagem'])) {
            unlink(BASE_PATH . '/' . $produto['imagem']); // Apaga o arquivo da imagem
        }
    }
    $stmt_img->close();

    // Agora, deleta o registro do produto no banco
    $stmt = $conn->prepare("DELETE FROM produtos WHERE id = ?");
    $stmt->bind_param("i", $id_para_deletar);
    if ($stmt->execute()) {
        $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Produto e imagem associada excluídos com sucesso!'];
    } else {
        $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao excluir o produto.'];
    }
    $stmt->close();
    
    header('Location: ' . BASE_URL . '/admin/produtos.php');
    exit;
}

// Lógica para ADICIONAR um novo produto com UPLOAD DE IMAGEM
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    if (empty($_POST['nome']) || empty($_POST['descricao']) || !isset($_POST['preco'])) {
        $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Nome, descrição e preço são obrigatórios.'];
    } else {
        $nome = $_POST['nome'];
        $descricao = $_POST['descricao'];
        $preco = floatval($_POST['preco']);
        $caminho_imagem = ''; // Inicia o caminho da imagem como vazio

        // --- LÓGICA DE UPLOAD DA IMAGEM ---
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
            $upload_dir = BASE_PATH . '/uploads/';
            $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
            $nome_arquivo_unico = uniqid('produto_', true) . '.' . $extensao;
            $caminho_destino = $upload_dir . $nome_arquivo_unico;
            
            // Validações de segurança do arquivo
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
            $tipo_mime_permitido = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (in_array($extensao, $extensoes_permitidas) && in_array($_FILES['imagem']['type'], $tipo_mime_permitido) && $_FILES['imagem']['size'] < 2000000) { // 2MB
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho_destino)) {
                    $caminho_imagem = 'uploads/' . $nome_arquivo_unico; // Caminho relativo para salvar no banco
                } else {
                    $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao mover o arquivo da imagem.'];
                    header('Location: ' . BASE_URL . '/admin/produtos.php');
                    exit;
                }
            } else {
                $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Arquivo de imagem inválido. Apenas JPG, PNG, GIF são permitidos e o tamanho deve ser menor que 2MB.'];
                header('Location: ' . BASE_URL . '/admin/produtos.php');
                exit;
            }
        }
        // --- FIM DA LÓGICA DE UPLOAD ---

        $stmt = $conn->prepare("INSERT INTO produtos (nome, descricao, preco, imagem) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $nome, $descricao, $preco, $caminho_imagem);

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Produto adicionado com sucesso!'];
        } else {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao adicionar o produto.'];
            // Se deu erro no banco, mas a imagem já foi enviada, apaga a imagem órfã
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
    <div class="card-body">
        <h5 class="card-title">Adicionar Novo Produto</h5>
        <form method="post" action="<?= BASE_URL ?>/admin/produtos.php" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" id="nome" name="nome" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea id="descricao" name="descricao" class="form-control" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="preco" class="form-label">Preço (ex: 99.90)</label>
                <input type="number" step="0.01" id="preco" name="preco" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="imagem" class="form-label">Imagem do Produto</label>
                <input type="file" id="imagem" name="imagem" class="form-control">
            </div>
            <button class="btn btn-success" type="submit" name="add_product">Adicionar Produto</button>
        </form>
    </div>
</div>

<hr>

<h2>Produtos Atuais</h2>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Imagem</th>
            <th>Produto</th>
            <th>Preço</th>
            <th class="text-end">Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $res = $conn->query("SELECT * FROM produtos ORDER BY id DESC");
        while ($p = $res->fetch_assoc()) :
        ?>
            <tr>
                <td>
                    <?php if(!empty($p['imagem'])): ?>
                        <img src="<?= BASE_URL . '/' . htmlspecialchars($p['imagem']) ?>" alt="<?= htmlspecialchars($p['nome']) ?>" width="50">
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($p['nome']) ?></td>
                <td>R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
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

<?php
require_once(BASE_PATH . '/includes/footer.php');
?>