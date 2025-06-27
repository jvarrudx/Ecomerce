<?php
// 1. Inclui os arquivos de configuração e banco de dados de forma segura
require_once(__DIR__ . '/includes/config.php');
require_once(__DIR__ . '/includes/db.php');

// 2. Validação robusta do ID do produto
// Verifica se o ID foi passado, se é um número e se é válido
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT) || $_GET['id'] <= 0) {
    // Redireciona para a página inicial se o ID for inválido
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}
$id = intval($_GET['id']);

// 3. SQL Injection CORRIGIDO com Prepared Statements
$stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ?");
$stmt->bind_param("i", $id); // 'i' para integer
$stmt->execute();
$resultado = $stmt->get_result();
$p = $resultado->fetch_assoc();
$stmt->close();

// 4. Verifica se o produto foi encontrado no banco de dados
// Se não, redireciona para a página inicial
if (!$p) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// 5. Inclui o header APÓS a lógica e validações
require_once(BASE_PATH . '/includes/header.php');
?>
<div class="row">
    <div class="col-md-6">
        <?php if (!empty($p['imagem'])) : ?>
            <img src="<?= BASE_URL . '/' . htmlspecialchars($p['imagem']) ?>" class="img-fluid" alt="<?= htmlspecialchars($p['nome']) ?>">
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <h1><?= htmlspecialchars($p['nome']) ?></h1>
        
        <p><?= nl2br(htmlspecialchars($p['descricao'])) ?></p>
        
        <p class="fs-4 fw-bold">R$ <?= number_format($p['preco'], 2, ',', '.') ?></p>
        
        <a href="<?= BASE_URL ?>/carrinho.php?add=<?= $p['id'] ?>" class="btn btn-success">
            <img src="https://cdn-icons-png.flaticon.com/512/833/833314.png" width="16" alt="Ícone de carrinho"> Adicionar ao Carrinho
        </a>
    </div>
</div>
<?php
// 9. Inclui o footer
require_once(BASE_PATH . '/includes/footer.php');
?>