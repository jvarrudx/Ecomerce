<?php
include("includes/header.php");
include("includes/db.php");
?>
<h1 class="mb-4">Produtos</h1>
<div class="row">
<?php
$res = $conn->query("SELECT * FROM produtos");
while($p = $res->fetch_assoc()):
?>
  <div class="col-md-4 mb-4">
    <div class="card h-100">
      <?php if($p['imagem']): ?>
      <img src="<?= $p['imagem'] ?>" class="card-img-top" alt="<?= $p['nome'] ?>">
      <?php endif; ?>
      <div class="card-body d-flex flex-column">
        <h5 class="card-title"><?= $p['nome'] ?></h5>
        <p class="card-text"><?= substr($p['descricao'],0,100) ?>...</p>
        <div class="mt-auto">
          <p class="fw-bold">R$ <?= number_format($p['preco'],2,',','.') ?></p>
          <a href="produto.php?id=<?= $p['id'] ?>" class="btn btn-primary">
            <img src="https://cdn-icons-png.flaticon.com/512/833/833314.png" width="16"> Ver
          </a>
        </div>
      </div>
    </div>
  </div>
<?php endwhile; ?>
</div>
<?php include("includes/footer.php"); ?>