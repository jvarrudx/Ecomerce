<?php
// Não precisa mais do config.php aqui, vamos incluir ambos nos arquivos principais
$host = "localhost";
$user = "root";
$pass = "";
$db = "ecommerce"; // Substitua pelo nome real do seu banco

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}
?>