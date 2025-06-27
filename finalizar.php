<?php
// 1. Inclui os arquivos de configuração e banco de dados
require_once(__DIR__ . '/includes/config.php');
require_once(__DIR__ . '/includes/db.php');

// 2. Validações Iniciais
// Se o usuário não estiver logado, redireciona para o login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Se o carrinho estiver vazio, redireciona para a página inicial
if (empty($_SESSION['carrinho'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Inicia uma transação. Ou tudo funciona, ou nada é salvo no banco.
$conn->begin_transaction();

try {
    // 3. Recalcula o total do pedido de forma SEGURA e EFICIENTE
    $total = 0;
    $ids_dos_produtos = array_keys($_SESSION['carrinho']);
    
    // Prepara a consulta para buscar todos os produtos de uma só vez (Evita N+1)
    $placeholders = implode(',', array_fill(0, count($ids_dos_produtos), '?'));
    
    $stmt_select = $conn->prepare("SELECT id, preco FROM produtos WHERE id IN ($placeholders)");
    $stmt_select->bind_param(str_repeat('i', count($ids_dos_produtos)), ...$ids_dos_produtos);
    $stmt_select->execute();
    $resultado = $stmt_select->get_result();
    
    $produtos_db = [];
    while ($p = $resultado->fetch_assoc()) {
        $produtos_db[$p['id']] = $p;
    }

    // Calcula o total com base nos preços atuais do banco de dados
    foreach ($_SESSION['carrinho'] as $id => $qtd) {
        if (isset($produtos_db[$id])) {
            $total += $produtos_db[$id]['preco'] * $qtd;
        }
    }

    // 4. Insere o pedido no banco de forma SEGURA
    $stmt_insert = $conn->prepare("INSERT INTO pedidos (usuario_id, total) VALUES (?, ?)");
    $stmt_insert->bind_param("id", $_SESSION['usuario_id'], $total); // i=integer, d=double (para valor monetário)
    $stmt_insert->execute();

    // 5. Se tudo deu certo até aqui, confirma as operações no banco
    $conn->