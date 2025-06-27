<?php
session_start();
include("includes/header.php");
include("includes/db.php");
$isAdmin = isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo']==='admin';
if($_POST){
    $nome=$conn->real_escape_string($_POST['nome']);
    $email=$conn->real_escape_string($_POST['email']);
    $senha=password_hash($_POST['senha'],PASSWORD_DEFAULT);
    $tipo=($isAdmin && isset($_POST['tipo']))?$_POST['tipo']:'normal';
    $conn->query("INSERT INTO usuarios (nome,email,senha,tipo) VALUES ('$nome','$email','$senha','$tipo')");
    echo '<div class="alert alert-success">Cadastro realizado!</div>';
}
?>
<h1>Cadastro</h1>
<form method="post">
  <div class="mb-3">
    <label class="form-label">Nome</label>
    <input type="text" name="nome" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">E-mail</label>
    <input type="email" name="email" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Senha</label>
    <input type="password" name="senha" class="form-control" required>
  </div>
  <?php if($isAdmin): ?>
  <div class="mb-3">
    <label class="form-label">Tipo</label>
    <select name="tipo" class="form-select">
      <option value="normal">Normal</option>
      <option value="admin">Administrador</option>
    </select>
  </div>
  <?php endif; ?>
  <button class="btn btn-primary" type="submit">Cadastrar</button>
</form>
<?php include("includes/footer.php"); ?>