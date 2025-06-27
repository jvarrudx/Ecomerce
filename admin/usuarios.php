<?php
session_start();
include("../includes/db.php");
include("../includes/functions.php");
if(!ehAdmin()){ die("Acesso negado."); }
if(isset($_GET['del'])){
    $id=intval($_GET['del']);
    $conn->query("DELETE FROM usuarios WHERE id=$id");
}
include("../includes/header.php");
?>
<h1>Gerenciar Usuários</h1>
<table class="table">
<thead><tr><th>Nome</th><th>E-mail</th><th>Tipo</th><th>Ações</th></tr></thead>
<tbody>
<?php
$res=$conn->query("SELECT * FROM usuarios");
while($u=$res->fetch_assoc()):
?>
<tr>
  <td><?= $u['nome'] ?></td>
  <td><?= $u['email'] ?></td>
  <td><?= ucfirst($u['tipo']) ?></td>
  <td><a href="?del=<?= $u['id'] ?>" class="btn btn-sm btn-danger">Excluir</a></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<?php include("../includes/footer.php"); ?>