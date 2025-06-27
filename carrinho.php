
<?php
// 1. Inclui o config.php, que inicia a sessão e define as constantes de caminho
require_once(__DIR__ . '/includes/config.php');
require_once(__DIR__ . '/includes/db.php');

// 2. Lógica para REMOVER um item do carrinho
// Validação do ID e uso de BASE_URL para o redirecionamento
if (isset($_GET['del']) && filter_var($_GET['del'], FILTER_VALIDATE_INT)) {
    $id_para_remover = intval($_GET['del']);
    unset($_SESSION['carrinho'][$id_para_remover]);
    header('Location: ' . BASE_URL . '/carrinho.php');
    exit;
}

// 3. Lógica para ADICIONAR um item ao carrinho
// Validação do ID e uso de BASE_URL para o redirecionamento
if (isset($_GET['add']) && filter_var($_GET['add'], FILTER_VALIDATE_INT)) {
    $id_para_adicionar = intval($_GET['add']);
    $_SESSION['carrinho'][$id_para_adicionar] = ($_SESSION['carrinho'][$id_para_adicionar] ?? 0) + 1;
    header('Location: ' . BASE_URL . '/carrinho.php');
    exit;
}

// 4. Inclui o header usando o caminho absoluto
require_once(BASE_PATH . '/includes/header.php');
?>

<h1 class="mb-4">Carrinho de Compras</h1>

<?php if (empty($_SESSION['carrinho'])) : ?>
    <div class="alert alert-info">Seu carrinho está vazio.</div>
<?php else : ?>
    <?php
    // O seu código de otimização já está excelente, mantivemos ele intacto.
    $ids_dos_produtos = array_keys($_SESSION['carrinho']);
    $produtos = [];

    if (!empty($ids_dos_produtos)) {
        $placeholders = implode(',', array_fill(0, count($ids_dos_produtos), '?'));
        $stmt = $conn->prepare("SELECT id, nome, preco FROM produtos WHERE id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($ids_dos_produtos)), ...$ids_dos_produtos);
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($produto = $resultado->fetch_assoc()) {
            $produtos[$produto['id']] = $produto;
        }
        $stmt->close();
    }
    ?>
    <table class="table">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Subtotal</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total = 0;
            foreach ($_SESSION['carrinho'] as $id => $qtd) :
                if (isset($produtos[$id])) :
                    $prod = $produtos[$id];
                    $subtotal = $prod['preco'] * $qtd;
                    $total += $subtotal;
            ?>
                    <tr>
                        <td><?= htmlspecialchars($prod['nome']) ?></td>
                        <td><?= $qtd ?></td>
                        <td>R$ <?= number_format($subtotal, 2, ',', '.') ?></td>
                        <td><a href="<?= BASE_URL ?>/carrinho.php?del=<?= $id ?>" class="btn btn-sm btn-danger">Remover</a></td>
                    </tr>
            <?php
                endif;
            endforeach;
            ?>
        </tbody>
    </table>
    <div class="d-flex justify-content-between align-items-center">
        <h4>Total: R$ <?= number_format($total, 2, ',', '.') ?></h4>
        <a href="<?= BASE_URL ?>/finalizar.php" class="btn btn-primary">Finalizar Pedido</a>
    </div>
<?php endif; ?>

<?php
// 7. Inclui o footer usando o caminho absoluto
require_once(BASE_PATH . '/includes/footer.php');
?>
```