<?php
// Inicia o buffer de saída
ob_start();

// Inicia a sessão
session_start();

// Habilita a exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- CONFIGURAÇÃO PRINCIPAL ---
define('BASE_URL', 'http://localhost/TRABALHO PROG2');
define('BASE_PATH', dirname(__DIR__));

// --- ESSA LINHA É A CHAVE ---
// Inclui o arquivo de funções para que elas fiquem disponíveis globalmente
require_once(BASE_PATH . '/includes/functions.php'); 
?>