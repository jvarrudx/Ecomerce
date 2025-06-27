<?php
session_start();
include("includes/db.php");
if(!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
$total=0;
foreach($_SESSION['carrinho'] as $id=>$qtd){
    $res=$conn->query("SELECT preco FROM produtos WHERE id=$id");
    $p=$res->fetch_assoc();
    $total+=$p['preco']*$qtd;
}
$conn->query("INSERT INTO pedidos (usuario_id,total) VALUES ({$_SESSION['usuario_id']},$total)");
unset($_SESSION['carrinho']);
include("includes/header.php");
?>
<h1>Pedido Finalizado!</h1>
<p>Obrigado pela compra. Total: R$ <?= number_format($total,2,',','.') ?></p>
<a href="index.php" class="btn btn-primary">Voltar à Loja</a>
<?php include("includes/footer.php"); ?>