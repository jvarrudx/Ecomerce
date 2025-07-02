<?php
// 1. Inclui o config.php PRIMEIRO. Ele prepara tudo.
require_once(__DIR__ . '/includes/config.php');

// 2. Inclui a conexão com o banco de dados.
require_once(__DIR__ . '/includes/db.php');

// 3. AGORA, inclui o header.
require_once(BASE_PATH . '/includes/header.php');

// Em index.php
$res = $conn->query("SELECT * FROM produtos WHERE status = 'ativo' ORDER BY nome ASC");
?>

<h1 class="mb-4">Produtos</h1>
<div class="row">
<?php
// Verifica se a consulta retornou resultados
if ($res->num_rows > 0):
    while($p = $res->fetch_assoc()):
?>
  <div class="col-md-3 mb-4">
    <div class="card h-100 product-card">
      <?php if(!empty($p['imagem'])): ?>
        <img src="<?= BASE_URL . '/' . htmlspecialchars($p['imagem']) ?>" class="card-img-top" alt="<?= htmlspecialchars($p['nome']) ?>">
      <?php endif; ?>
      
      <div class="card-body d-flex flex-column">
        <h5 class="card-title"><?= htmlspecialchars($p['nome']) ?></h5>
        
        <div class="mt-auto">
          <p class="fw-bold">R$ <?= number_format($p['preco'], 2, ',', '.') ?></p>
          
          <div class="mb-2">
            <?php if ($p['estoque'] > 0): ?>
                <span class="badge bg-success">Em estoque: <?= $p['estoque'] ?></span>
            <?php else: ?>
                <span class="badge bg-danger">Esgotado!</span>
            <?php endif; ?>
          </div>
          
          <a href="<?= BASE_URL ?>/produto.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm">
             Ver Detalhes
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
// Footer incluído usando a constante BASE_PATH
require_once(BASE_PATH . '/includes/footer.php');
?>