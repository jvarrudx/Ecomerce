<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Incluindo o config.php para ter acesso ao BASE_URL
require_once(__DIR__ . '/config.php');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu E-commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">

    <script>
        (function() {
            const theme = localStorage.getItem('theme');
            if (theme === 'dark') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
      <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png" alt="Home" width="30">
      Inicio
    </a>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-center">
        <?php if(isset($_SESSION['usuario_id'])): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/carrinho.php">Carrinho</a>
          </li>
          <?php if(ehAdmin()): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Admin</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/produtos.php">Gerenciar Produtos</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/usuarios.php">Gerenciar Usuários</a></li>
            </ul>
          </li>
          <?php endif; ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/logout.php">Sair</a>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/cadastro.php">Cadastrar</a></li>
        <?php endif; ?>
        
        <li class="nav-item ms-3">
            <button class="theme-switcher" id="theme-toggle" title="Alterar tema">
                <i class="fas fa-moon icon-moon"></i>
                <i class="fas fa-sun icon-sun"></i>
            </button>
        </li>
      </ul>
    </div>
  </div>
</nav>
<div class="container">