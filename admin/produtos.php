<?php
session_start();
include("../includes/db.php");
include("../includes/functions.php");
if(!ehAdmin()){ die("Acesso negado."); }
if($_POST){
    $nome=$conn->real_escape_string($_POST['nome']);
    $descricao=$conn->real_escape_string($_POST['descricao']);
    $preco=floatval($_POST['preco']);
    $imagem=$conn->real_escape_string($_POST['imagem']);
    $conn->query("INSERT INTO produtos (nome,descricao,preco,imagem) VALUES ('$nome','$descricao',$preco,'$imagem')");
}
if(isset($_GET['del'])){
    $id=intval($_GET['del']);
    $conn->query("DELETE FROM produtos WHERE id=$id");
}
include("../includes/header.php");
?>
<h1>Gerenciar Produtos</h1>
<form method="post">
  <div class="mb-3"><label class="form-label">Nome</label><input name="nome" class="form-control" required></div>
  <div class="mb-3"><label class="form-label">Descrição</label><textarea name="descricao" class="form-control" required></textarea></div>
  <div class="mb-3"><label class="form-label">Preço</label><input name="preco" class="form-control" required></div>
  <div class="mb-3"><label class="form-label">URL Imagem</label><input name="imagem" class="form-control"></div>
  <button class="btn btn-success" type="submit">Adicionar</button>
</form>
<hr>
<table class="table">
<thead><tr><th>Produto</th><th>Preço</th><th>Ações</th></tr></thead>
<tbody>
<?php
$res=$conn->query("SELECT * FROM produtos");
while($p=$res->fetch_assoc()):
?>
<tr>
  <td><?= $p['nome'] ?></td>
  <td>R$ <?= number_format($p['preco'],2,',','.') ?></td>
  <td><a href="?del=<?= $p['id'] ?>" class="btn btn-sm btn-danger">Excluir</a></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<?php include("../includes/footer.php"); ?>