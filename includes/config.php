<?php
// Inicia o buffer de saída para evitar erros de "headers already sent"
ob_start();

// Inicia a sessão aqui para que esteja disponível em todas as páginas
session_start();

// Habilita a exibição de todos os erros (bom para desenvolvimento)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- CONFIGURAÇÃO PRINCIPAL ---

// Define a URL base para links, imagens, CSS, etc.
// Altere se o nome da sua pasta for diferente
define('BASE_URL', 'http://localhost/TRABALHO PROG2');

// Define o caminho base no servidor para includes e requires em PHP
// __DIR__ pega o diretório do arquivo atual (includes) e '/..' sobe um nível.
define('BASE_PATH', dirname(__DIR__));

?>