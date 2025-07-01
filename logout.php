<?php
// 1. Inclui o arquivo de configuração para iniciar a sessão
require_once(__DIR__ . '/includes/config.php');

// 2. Destrói todos os dados registrados na sessão atual
session_destroy();

// 3. Redireciona o usuário para a nova PÁGINA DE CONFIRMAÇÃO
header('Location: ' . BASE_URL . '/logout-sucesso.php');
exit;
?>