<?php
include("includes/header.php");
include("includes/db.php");
$id = intval($_GET['id']);
$res = $conn->query("SELECT * FROM produtos WHERE id=$id");
$p = $res->fetch_assoc();
?>
<div class="row">
  <div class="col-md-6">
    <?php if($p['imagem']): ?>
    <img src="<?= $p['imagem'] ?>" class="img-fluid" alt="<?= $p['nome'] ?>">
    <?php endif; ?>
  </div>
  <div class="col-md-6">
    <h1><?= $p['nome'] ?></h1>
    <p><?= $p['descricao'] ?></p>
    <p class="fs-4 fw-bold">R$ <?= number_format($p['preco'],2,',','.') ?></p>
    <a href="carrinho.php?add=<?= $p['id'] ?>" class="btn btn-success">
      <img src="https://cdn-icons-png.flaticon.com/512/833/833314.png" width="16"> Adicionar ao Carrinho
    </a>
  </div>
</div>
<?php include("includes/footer.php"); ?>