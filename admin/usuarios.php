<?php
// 1. Configuração e Segurança Inicial
require_once(dirname(__DIR__) . '/includes/config.php');
require_once(dirname(__DIR__) . '/includes/db.php');
require_once(BASE_PATH . '/includes/functions.php');

// Protege a página, permitindo acesso apenas para administradores
if (!ehAdmin()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// 2. Lógica para DELETAR um usuário (usando POST e com salvaguardas)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $id_para_deletar = intval($_POST['delete_id']);

    // 3. SALVAGUARDA CRÍTICA: Impede que um admin delete a própria conta
    if ($id_para_deletar === $_SESSION['usuario_id']) {
        $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Ação não permitida! Você não pode excluir sua própria conta.'];
    } else {
        // Exclusão segura com Prepared Statements
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id_para_deletar);
        
        if ($stmt->execute()) {
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Usuário excluído com sucesso!'];
        } else {
            $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao excluir o usuário.'];
        }
        $stmt->close();
    }
    
    header('Location: ' . BASE_URL . '/admin/usuarios.php');
    exit;
}

// Inclui o header após toda a lógica
require_once(BASE_PATH . '/includes/header.php');
?>

<h1>Gerenciar Usuários</h1>
<p>Visualizar e remover usuários do sistema.</p>

<?php
// 4. Exibe a mensagem de feedback, se houver
if (isset($_SESSION['mensagem'])) {
    echo '<div class="alert alert-' . $_SESSION['mensagem']['tipo'] . '">' . htmlspecialchars($_SESSION['mensagem']['texto']) . '</div>';
    unset($_SESSION['mensagem']); // Limpa a mensagem para não ser exibida novamente
}
?>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Tipo</th>
            <th class="text-end">Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // A consulta é segura pois não usa input do usuário
        $res = $conn->query("SELECT id, nome, email, tipo FROM usuarios ORDER BY nome ASC");
        while ($u = $res->fetch_assoc()):
        ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['nome']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars(ucfirst($u['tipo'])) ?></td>
                <td class="text-end">
                    <?php
                    // 6. Impede que o botão "Excluir" apareça para o próprio admin logado
                    if ($u['id'] !== $_SESSION['usuario_id']):
                    ?>
                        <form method="post" action="<?= BASE_URL ?>/admin/usuarios.php" onsubmit="return confirm('Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.');" style="display: inline;">
                            <input type="hidden" name="delete_id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                        </form>
                    <?php else: ?>
                        <span class="badge bg-secondary">Você</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php
// Inclui o footer
require_once(BASE_PATH . '/includes/footer.php');
?>