<?php
session_start();
include("includes/header.php");
include("includes/db.php");
if(!isset($_SESSION['carrinho'])) $_SESSION['carrinho'] = [];
if(isset($_GET['add'])) {
    $id = intval($_GET['add']);
    $_SESSION['carrinho'][$id] = ($_SESSION['carrinho'][$id] ?? 0) + 1;
}
?>
<h1 class="mb-4">Carrinho</h1>
<?php if(empty($_SESSION['carrinho'])): ?>
  <p>Seu carrinho está vazio.</p>
<?php else: ?>
  <table class="table">
    <thead><tr><th>Produto</th><th>Qtd</th><th>Subtotal</th><th></th></tr></thead>
    <tbody>
    <?php
    $total = 0;
    foreach($_SESSION['carrinho'] as $id=>$qtd):
        $res=$conn->query("SELECT nome,preco FROM produtos WHERE id=$id");
        $prod=$res->fetch_assoc();
        $subtotal=$prod['preco']*$qtd;
        $total+=$subtotal;
    ?>
      <tr>
        <td><?= $prod['nome'] ?></td>
        <td><?= $qtd ?></td>
        <td>R$ <?= number_format($subtotal,2,',','.') ?></td>
        <td><a href="carrinho.php?del=<?= $id ?>" class="text-danger">Remover</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <div class="d-flex justify-content-between align-items-center">
    <h4>Total: R$ <?= number_format($total,2,',','.') ?></h4>
    <a href="finalizar.php" class="btn btn-primary">Finalizar Pedido</a>
  </div>
<?php endif; ?>
<?php include("includes/footer.php"); ?>