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
    $produtos_db = [];

    // Prepara a consulta para buscar todos os produtos de uma só vez (Evita N+1)
    $placeholders = implode(',', array_fill(0, count($ids_dos_produtos), '?'));
    
    $stmt_select = $conn->prepare("SELECT id, preco FROM produtos WHERE id IN ($placeholders)");
    $stmt_select->bind_param(str_repeat('i', count($ids_dos_produtos)), ...$ids_dos_produtos);
    $stmt_select->execute();
    $resultado = $stmt_select->get_result();
    
    while ($p = $resultado->fetch_assoc()) {
        $produtos_db[$p['id']] = $p;
    }

    // Calcula o total com base nos preços atuais do banco de dados
    foreach ($_SESSION['carrinho'] as $id => $qtd) {
        // Garante que o produto ainda existe no banco antes de somar
        if (isset($produtos_db[$id])) {
            $total += $produtos_db[$id]['preco'] * $qtd;
        }
    }
    $stmt_select->close();

    // 4. Insere o pedido no banco de forma SEGURA com Prepared Statement
    $stmt_insert = $conn->prepare("INSERT INTO pedidos (usuario_id, total, data_pedido) VALUES (?, ?, NOW())");
    $stmt_insert->bind_param("id", $_SESSION['usuario_id'], $total); // i=integer, d=double (para valor monetário)
    
    // Executa a inserção e verifica se foi bem sucedida
    if (!$stmt_insert->execute()) {
        throw new Exception("Falha ao inserir o pedido no banco de dados.");
    }
    $stmt_insert->close();

    // 5. Se tudo deu certo até aqui, confirma as operações no banco (torna permanente)
    $conn->commit();

    // 6. Apenas após confirmar o pedido, limpa o carrinho da sessão
    unset($_SESSION['carrinho']);

    // Define uma mensagem de sucesso para exibir na página
    $mensagem = "Obrigado pela sua compra. Seu pedido foi registrado com sucesso!";
    $tipo_mensagem = "success";

} catch (Exception $e) {
    // 7. Se qualquer passo falhar, desfaz TODAS as operações no banco feitas dentro da transação
    $conn->rollback();
    
    // Define uma mensagem de erro para o usuário
    $mensagem = "Ocorreu um erro ao processar seu pedido. Por favor, tente novamente mais tarde.";
    $tipo_mensagem = "danger";
    
    // Opcional: registrar o erro real em um log de sistema para o desenvolvedor
    // error_log('Erro ao finalizar pedido: ' . $e->getMessage());
}

// 8. Inclui o header APÓS toda a lógica
require_once(BASE_PATH . '/includes/header.php');
?>

<h1>Finalização do Pedido</h1>

<div class="alert alert-<?= $tipo_mensagem ?>">
    <?= htmlspecialchars($mensagem) ?>
</div>

<?php if ($tipo_mensagem === 'success'): ?>
    <p><b>Total do Pedido:</b> R$ <?= number_format($total, 2, ',', '.') ?></p>
<?php endif; ?>

<a href="<?= BASE_URL ?>/index.php" class="btn btn-primary">Voltar à Loja</a>

<?php require_once(BASE_PATH . '/includes/footer.php'); ?>