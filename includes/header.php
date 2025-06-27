<?php
// Esta verificação é uma boa prática, mas lembre-se que o ideal é que
// o seu arquivo config.php (com session_start()) seja sempre incluído antes deste header.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu E-commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
      <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png" alt="Home" width="30">
      Loja
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto">
        <?php if(isset($_SESSION['usuario_id'])): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/carrinho.php">
              <img src="https://cdn-icons-png.flaticon.com/512/833/833314.png" alt="Carrinho" width="24"> Carrinho
            </a>
          </li>
          <?php if(isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo']==='admin'): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
              <img src="https://cdn-icons-png.flaticon.com/512/709/709699.png" alt="Admin" width="24"> Admin
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/produtos.php">Gerenciar Produtos</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/usuarios.php">Gerenciar Usuários</a></li>
            </ul>
          </li>
          <?php endif; ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/logout.php">
              <img src="https://cdn-icons-png.flaticon.com/512/1077/1077114.png" alt="Logout" width="24"> Sair
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/cadastro.php">Cadastrar</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container">