<?php
// 1. Inclui o config.php PRIMEIRO. Ele prepara tudo, inclusive a função ehAdmin().
require_once(__DIR__ . '/includes/config.php');

// 2. Inclui a conexão com o banco de dados.
require_once(__DIR__ . '/includes/db.php');

// 3. AGORA, inclui o header, que pode usar com segurança as funções e constantes.
require_once(BASE_PATH . '/includes/header.php');

// A consulta SQL é segura, pois não utiliza nenhum dado vindo do usuário
$res = $conn->query("SELECT * FROM produtos ORDER BY nome ASC");
?>

<h1 class="mb-4">Produtos</h1>
<div class="row">
<?php
// Verifica se a consulta retornou resultados
if ($res->num_rows > 0):
    while($p = $res->fetch_assoc()):
?>
  <div class="col-md-4 mb-4">
    <div class="card h-100">
      <?php if(!empty($p['imagem'])): ?>
        <img src="<?= BASE_URL . '/' . htmlspecialchars($p['imagem']) ?>" class="card-img-top" alt="<?= htmlspecialchars($p['nome']) ?>">
      <?php endif; ?>
      <div class="card-body d-flex flex-column">
        <h5 class="card-title"><?= htmlspecialchars($p['nome']) ?></h5>
        <p class="card-text"><?= htmlspecialchars(substr($p['descricao'], 0, 100)) ?>...</p>
        <div class="mt-auto">
          <p class="fw-bold">R$ <?= number_format($p['preco'], 2, ',', '.') ?></p>
          <a href="<?= BASE_URL ?>/produto.php?id=<?= $p['id'] ?>" class="btn btn-primary">
            <img src="https://cdn-icons-png.flaticon.com/512/833/833314.png" width="16" alt="Ícone de carrinho"> Ver Detalhes
          </a>
        </div>
      </div>
    </div>
  </div>
<?php
    endwhile;
else:
?>
    <div class="col-12">
        <div class="alert alert-info">Nenhum produto encontrado.</div>
    </div>
<?php
endif;
?>
</div>

<?php
// 6. Footer incluído usando a constante BASE_PATH
require_once(BASE_PATH . '/includes/footer.php');
?>