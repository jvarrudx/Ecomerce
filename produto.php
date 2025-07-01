<?php
// Inclui os arquivos de configuração e banco de dados
require_once(__DIR__ . '/includes/config.php');
require_once(__DIR__ . '/includes/db.php');
require_once(BASE_PATH . '/includes/functions.php'); // Onde a função ehAdmin() está

// Validação do ID do produto
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}
$id = intval($_GET['id']);

// Busca segura do produto
$stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$p = $res->fetch_assoc();

if (!$p) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// --- LÓGICA DO BOTÃO ADICIONAR AO CARRINHO ---
// Verifica se o usuário está logado para definir o link do botão
if (isset($_SESSION['usuario_id'])) {
    // Se estiver logado, o link adiciona o produto ao carrinho
    $link_carrinho = BASE_URL . '/carrinho.php?add=' . $p['id'];
} else {
    // Se NÃO estiver logado, o link leva para a página de login.
    // Adicionamos um parâmetro 'redirect_url' para saber para onde voltar após o login.
    $redirect_url = urlencode($_SERVER['REQUEST_URI']); // Pega a URL atual (ex: /produto.php?id=123)
    $link_carrinho = BASE_URL . '/login.php?redirect_url=' . $redirect_url;
}
// --- FIM DA LÓGICA ---

require_once(BASE_PATH . '/includes/header.php');
?>
<div class="row">
  <div class="col-md-6">
    <?php if(!empty($p['imagem'])): ?>
    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($p['imagem']) ?>" class="img-fluid" alt="<?= htmlspecialchars($p['nome']) ?>">
    <?php endif; ?>
  </div>
  <div class="col-md-6">
    <h1><?= htmlspecialchars($p['nome']) ?></h1>
    <p><?= nl2br(htmlspecialchars($p['descricao'])) ?></p>
    <p class="fs-4 fw-bold">R$ <?= number_format($p['preco'], 2, ',', '.') ?></p>

    <a href="<?= $link_carrinho ?>" class="btn btn-success">
      <img src="https://cdn-icons-png.flaticon.com/512/833/833314.png" width="16" alt="Ícone de carrinho"> Adicionar ao Carrinho
    </a>
  </div>
</div>
<?php require_once(BASE_PATH . '/includes/footer.php'); ?>