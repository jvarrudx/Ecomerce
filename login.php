<?php
session_start();
include("includes/header.php");
include("includes/db.php");
if($_POST){
    $email=$conn->real_escape_string($_POST['email']);
    $senha=$_POST['senha'];
    $res=$conn->query("SELECT * FROM usuarios WHERE email='$email'");
    $user=$res->fetch_assoc();
    if($user && password_verify($senha,$user['senha'])){
        $_SESSION['usuario_id']=$user['id'];
        $_SESSION['usuario_tipo']=$user['tipo'];
        header('Location: index.php');
        exit;
    } else {
        echo '<div class="alert alert-danger">Dados inválidos.</div>';
    }
}
?>
<h1>Login</h1>
<form method="post">
  <div class="mb-3">
    <label class="form-label">E-mail</label>
    <input type="email" name="email" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Senha</label>
    <input type="password" name="senha" class="form-control" required>
  </div>
  <button class="btn btn-primary" type="submit">Entrar</button>
</form>
<?php include("includes/footer.php"); ?>