<?php
$conn = new mysqli("localhost", "root", "", "ecommerce");
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
?>