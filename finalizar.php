<?php
require_once(__DIR__ . '/includes/config.php');
require_once(__DIR__ . '/includes/db.php');

// Validações Iniciais
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}
if (empty($_SESSION['carrinho'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Inicia uma transação para garantir a integridade dos dados
$conn->begin_transaction();

$erro_estoque = null;

try {
    // 1. BUSCAR TODOS OS PRODUTOS DO CARRINHO DE UMA VEZ
    $ids_dos_produtos = array_keys($_SESSION['carrinho']);
    $produtos_no_carrinho = [];
    
    if (!empty($ids_dos_produtos)) {
        $placeholders = implode(',', array_fill(0, count($ids_dos_produtos), '?'));
        $stmt_select = $conn->prepare("SELECT id, nome, preco, estoque, vendedor_id FROM produtos WHERE id IN ($placeholders)");
        $stmt_select->bind_param(str_repeat('i', count($ids_dos_produtos)), ...$ids_dos_produtos);
        $stmt_select->execute();
        $resultado = $stmt_select->get_result();
        while ($produto = $resultado->fetch_assoc()) {
            $produtos_no_carrinho[$produto['id']] = $produto;
        }
    }

    // 2. VERIFICAR O ESTOQUE DE TODOS OS PRODUTOS ANTES DE PROSSEGUIR
    foreach ($_SESSION['carrinho'] as $id => $qtd) {
        if (!isset($produtos_no_carrinho[$id]) || $produtos_no_carrinho[$id]['estoque'] < $qtd) {
            // Se um produto não existe ou não tem estoque, guarda o nome e lança um erro
            $nome_produto = isset($produtos_no_carrinho[$id]) ? $produtos_no_carrinho[$id]['nome'] : "Produto #$id";
            throw new Exception("Desculpe, o produto '" . htmlspecialchars($nome_produto) . "' não tem estoque suficiente.");
        }
    }

    // 3. SE TODO O ESTOQUE ESTIVER OK, CALCULA O TOTAL E CRIA O PEDIDO PRINCIPAL
    $total_final = 0;
    foreach ($_SESSION['carrinho'] as $id => $qtd) {
        $total_final += $produtos_no_carrinho[$id]['preco'] * $qtd;
    }

    $stmt_pedido = $conn->prepare("INSERT INTO pedidos (usuario_id, total, data_pedido) VALUES (?, ?, NOW())");
    $stmt_pedido->bind_param("id", $_SESSION['usuario_id'], $total_final);
    $stmt_pedido->execute();
    $novo_pedido_id = $conn->insert_id; // Pega o ID do pedido que acabamos de criar

    // 4. INSERE CADA ITEM DO CARRINHO NA TABELA 'pedido_itens' E ATUALIZA O ESTOQUE
    $stmt_item = $conn->prepare("INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario, vendedor_id) VALUES (?, ?, ?, ?, ?)");
    $stmt_update_estoque = $conn->prepare("UPDATE produtos SET estoque = estoque - ? WHERE id = ?");

    foreach ($_SESSION['carrinho'] as $id => $qtd) {
        $produto = $produtos_no_carrinho[$id];
        
        // Insere o item na tabela pedido_itens
        $stmt_item->bind_param("iiidi", $novo_pedido_id, $id, $qtd, $produto['preco'], $produto['vendedor_id']);
        $stmt_item->execute();

        // Diminui o estoque na tabela produtos
        $stmt_update_estoque->bind_param("ii", $qtd, $id);
        $stmt_update_estoque->execute();
    }

    // 5. SE CHEGOU ATÉ AQUI SEM ERROS, CONFIRMA TODAS AS OPERAÇÕES
    $conn->commit();

    // 6. Limpa o carrinho da sessão, pois a compra foi um sucesso
    unset($_SESSION['carrinho']);

    $mensagem = "Obrigado pela sua compra. Seu pedido foi registrado com sucesso!";
    $tipo_mensagem = "success";

} catch (Exception $e) {
    // 7. SE OCORREU QUALQUER ERRO, DESFAZ TODAS AS OPERAÇÕES NO BANCO
    $conn->rollback();
    
    // Define a mensagem de erro para o usuário
    $mensagem = $e->getMessage(); // Mostra a mensagem de erro específica (ex: falta de estoque)
    $tipo_mensagem = "danger";
}

require_once(BASE_PATH . '/includes/header.php');
?>

<h1>Finalização do Pedido</h1>

<div class="alert alert-<?= $tipo_mensagem ?>">
    <?= htmlspecialchars($mensagem) ?>
</div>

<?php if ($tipo_mensagem === 'success'): ?>
    <p><b>Total do Pedido:</b> R$ <?= number_format($total_final, 2, ',', '.') ?></p>
<?php endif; ?>

<a href="<?= BASE_URL ?>/index.php" class="btn btn-primary">Voltar à Loja</a>

<?php require_once(BASE_PATH . '/includes/footer.php'); ?>