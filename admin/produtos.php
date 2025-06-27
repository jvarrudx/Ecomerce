<?php
// 1. Configuração e Segurança Inicial
// Usamos __DIR__ para navegar corretamente a partir da pasta /admin/ para a raiz
require_once(dirname(__DIR__) . '/includes/config.php');
require_once(dirname(__DIR__) . '/includes/db.php');
require_once(BASE_PATH . '/includes/functions.php');

// Protege a página, permitindo acesso apenas para administradores
if (!ehAdmin()) {
    // Redireciona para a página inicial se não for admin
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Lógica para DELETAR um produto (usando POST para segurança)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $id_para_deletar = intval($_POST['delete_id']);
    
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
    // Validação básica
    if (empty($_POST['nome']) || empty($_POST['descricao']) || !isset($_POST['preco'])) {
        $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Todos os campos, exceto imagem, são obrigatórios.'];
    } else {
        $nome = $_POST['nome'];
        $descricao = $_POST['descricao'];
        $preco = floatval($_POST['preco']);
        $imagem = $_POST['imagem']; // URL da imagem

        $stmt = $conn->prepare("INSERT INTO produtos (nome, descricao, preco, imagem) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $nome, $descricao, $preco, $imagem); // s=string, d=double

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

// Inclui o header após toda a lógica de processamento
require_once(BASE_PATH . '/includes/header.php');
?>

<h1>Gerenciar Produtos</h1>
<p>Página para adicionar e remover produtos da loja.</p>

<?php
// Exibe a mensagem de feedback, se houver
if (isset($_SESSION['mensagem'])) {
    echo '<div class="alert alert-' . $_SESSION['mensagem']['tipo'] . '">' . htmlspecialchars($_SESSION['mensagem']['texto']) . '</div>';
    // Limpa a mensagem para não ser exibida novamente
    unset($_SESSION['mensagem']);
}
?>

<div class="card bg-light mb-4">
    <div class="card-body">
        <h5 class="card-title">Adicionar Novo Produto</h5>
        <form method="post" action="<?= BASE_URL ?>/admin/produtos.php">
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
                <label for="imagem" class="form-label">Caminho/URL da Imagem (ex: uploads/produto.jpg)</label>
                <input type="text" id="imagem" name="imagem" class="form-control">
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
// Inclui o footer
require_once(BASE_PATH . '/includes/footer.php');
?>