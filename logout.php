<?php
// 1. Inclui o arquivo de configuração para ter acesso à constante BASE_URL
// e para garantir que a sessão seja iniciada corretamente antes de ser destruída.
require_once(__DIR__ . '/includes/config.php');

// 2. Destrói todos os dados registrados na sessão atual.
// Esta é a função que efetivamente "desloga" o usuário.
session_destroy();

// 3. Redireciona o usuário para a página inicial usando o caminho absoluto.
// Isso garante que o redirecionamento funcione de qualquer lugar do site.
header('Location: ' . BASE_URL . '/index.php');
exit; // Garante que nenhum outro código seja executado após o redirecionamento.
?>