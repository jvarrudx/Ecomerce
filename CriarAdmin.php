<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "ecommerce"; // Substitua pelo nome real do seu banco

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$nome = "João ";
$email = "admin@admin.com";
$senha = password_hash("admin", PASSWORD_DEFAULT); // senha criptografada
$tipo = "admin";

$sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $nome, $email, $senha, $tipo);

if ($stmt->execute()) {
    echo "Usuário administrador criado com sucesso!";
} else {
    echo "Erro ao criar administrador: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
