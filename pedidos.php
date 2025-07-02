<?php
require_once(__DIR__ . '/includes/config.php');
require_once(__DIR__ . '/includes/db.php');
require_once(BASE_PATH . '/includes/functions.php');

// Apenas usuários logados podem ver esta página
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// --- LÓGICA PARA BUSCAR OS PEDIDOS ---

$pedidos_agrupados = [];
$titulo_pagina = "Meus Pedidos"; // Título padrão para clientes

// 1. Constrói a base da consulta SQL com todos os JOINs necessários
$sql_base = "SELECT 
                p.id AS pedido_id,
                p.data_pedido,
                p.total AS total_pedido,
                pi.quantidade,
                pi.preco_unitario,
                prod.nome AS nome_produto,
                cliente.nome AS nome_cliente,
                vendedor.nome AS nome_vendedor
            FROM pedidos p
            JOIN pedido_itens pi ON p.id = pi.pedido_id
            JOIN produtos prod ON pi.produto_id = prod.id
            JOIN usuarios cliente ON p.usuario_id = cliente.id
            LEFT JOIN usuarios vendedor ON pi.vendedor_id = vendedor.id";

$params = [];
$types = "";

// 2. Adiciona as condições WHERE de acordo com o tipo de usuário
if (ehAdmin()) {
    $titulo_pagina = "Todos os Pedidos";
    // Admin vê tudo, nenhuma condição WHERE necessária
    $sql_final = $sql_base . " ORDER BY p.data_pedido DESC";
    $stmt = $conn->prepare($sql_final);

} elseif (ehVendedor()) {
    $titulo_pagina = "Minhas Vendas";
    // Vendedor vê apenas os itens que ele vendeu
    $sql_final = $sql_base . " WHERE pi.vendedor_id = ? ORDER BY p.data_pedido DESC";
    $stmt = $conn->prepare($sql_final);
    $params[] = $_SESSION['usuario_id'];
    $types .= "i";
    $stmt->bind_param($types, ...$params);

} else { // Usuário 'normal' (cliente)
    // Cliente vê apenas os seus próprios pedidos
    $sql_final = $sql_base . " WHERE p.usuario_id = ? ORDER BY p.data_pedido DESC";
    $stmt = $conn->prepare($sql_final);
    $params[] = $_SESSION['usuario_id'];
    $types .= "i";
    $stmt->bind_param($types, ...$params);
}

// 3. Executa a consulta e agrupa os resultados
$stmt->execute();
$resultado = $stmt->get_result();

while ($item = $resultado->fetch_assoc()) {
    $pedido_id = $item['pedido_id'];
    if (!isset($pedidos_agrupados[$pedido_id])) {
        $pedidos_agrupados[$pedido_id] = [
            'data_pedido' => $item['data_pedido'],
            'total_pedido' => $item['total_pedido'],
            'nome_cliente' => $item['nome_cliente'],
            'itens' => []
        ];
    }
    $pedidos_agrupados[$pedido_id]['itens'][] = $item;
}
$stmt->close();

require_once(BASE_PATH . '/includes/header.php');
?>

<h1 class="mb-4"><?= $titulo_pagina ?></h1>

<?php if (empty($pedidos_agrupados)): ?>
    <div class="alert alert-info">
        <?php 
            if(ehVendedor() && !ehAdmin()) echo "Nenhuma venda registrada ainda.";
            else echo "Nenhum pedido encontrado.";
        ?>
    </div>
<?php else: ?>
    <div class="accordion" id="accordionPedidos">
        <?php foreach ($pedidos_agrupados as $pedido_id => $pedido): ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading-<?= $pedido_id ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $pedido_id ?>">
                        <div class="w-100 d-flex justify-content-between">
                            <span>Pedido #<?= $pedido_id ?></span>
                            <?php if (ehVendedor()): // Mostra o nome do cliente para vendedores e admin ?>
                                <span>Cliente: <?= htmlspecialchars($pedido['nome_cliente']) ?></span>
                            <?php endif; ?>
                            <span>Data: <?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?></span>
                            <span>Total: R$ <?= number_format($pedido['total_pedido'], 2, ',', '.') ?></span>
                        </div>
                    </button>
                </h2>
                <div id="collapse-<?= $pedido_id ?>" class="accordion-collapse collapse" data-bs-parent="#accordionPedidos">
                    <div class="accordion-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <?php if (ehAdmin()): // Apenas o admin principal vê o vendedor ?>
                                        <th>Vendedor</th>
                                    <?php endif; ?>
                                    <th class="text-center">Quantidade</th>
                                    <th class="text-end">Preço Unit.</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedido['itens'] as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['nome_produto']) ?></td>
                                        <?php if (ehAdmin()): ?>
                                            <td><?= htmlspecialchars($item['nome_vendedor'] ?? 'N/A') ?></td>
                                        <?php endif; ?>
                                        <td class="text-center"><?= $item['quantidade'] ?></td>
                                        <td class="text-end">R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?></td>
                                        <td class="text-end">R$ <?= number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once(BASE_PATH . '/includes/footer.php'); ?>