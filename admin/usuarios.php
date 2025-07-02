<?php
// 1. Configuração e Segurança Inicial
require_once(dirname(__DIR__) . '/includes/config.php');
require_once(dirname(__DIR__) . '/includes/db.php');
require_once(BASE_PATH . '/includes/functions.php');

// No topo de admin/usuarios.php
if (!ehAdmin()) { // Correto: Apenas o admin principal pode acessar
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// --- LÓGICA DE AÇÕES ---

// Lógica para REBAIXAR um vendedor para normal (Apenas o Admin pode fazer)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['demote_id']) && ehAdmin()) {
    $id_para_rebaixar = intval($_POST['demote_id']);
    $stmt = $conn->prepare("UPDATE usuarios SET tipo = 'normal' WHERE id = ? AND tipo = 'vendedor'");
    $stmt->bind_param("i", $id_para_rebaixar);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Vendedor rebaixado para usuário Normal.'];
    } else {
        $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao rebaixar vendedor.'];
    }
    $stmt->close();
    header('Location: ' . BASE_URL . '/admin/usuarios.php');
    exit;
}

// Lógica para PROMOVER um usuário para vendedor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['promote_id'])) {
    $id_para_promover = intval($_POST['promote_id']);
    $stmt = $conn->prepare("UPDATE usuarios SET tipo = 'vendedor' WHERE id = ? AND tipo = 'normal'");
    $stmt->bind_param("i", $id_para_promover);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Usuário promovido a Vendedor!'];
    } else {
        $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Erro ao promover usuário.'];
    }
    $stmt->close();
    header('Location: ' . BASE_URL . '/admin/usuarios.php');
    exit;
}

// Lógica para DELETAR um usuário
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $id_para_deletar = intval($_POST['delete_id']);
    $pode_deletar = false;

    // Admin pode deletar qualquer um, exceto ele mesmo.
    if (ehAdmin() && $id_para_deletar !== $_SESSION['usuario_id']) {
        $pode_deletar = true;
    }
    // Vendedor pode deletar apenas usuários normais.
    elseif (ehVendedor() && !ehAdmin()) {
        $stmt_check = $conn->prepare("SELECT tipo FROM usuarios WHERE id = ?");
        $stmt_check->bind_param("i", $id_para_deletar);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();
        if ($user_to_delete = $res_check->fetch_assoc()) {
            if ($user_to_delete['tipo'] === 'normal') {
                $pode_deletar = true;
            }
        }
    }

    if ($pode_deletar) {
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id_para_deletar);
        $_SESSION['mensagem'] = $stmt->execute() ? ['tipo' => 'success', 'texto' => 'Usuário excluído com sucesso!'] : ['tipo' => 'danger', 'texto' => 'Erro ao excluir o usuário.'];
        $stmt->close();
    } else {
        $_SESSION['mensagem'] = ['tipo' => 'danger', 'texto' => 'Ação não permitida! Você não tem permissão para excluir este tipo de usuário.'];
    }
    
    header('Location: ' . BASE_URL . '/admin/usuarios.php');
    exit;
}

require_once(BASE_PATH . '/includes/header.php');
?>

<h1>Gerenciar Usuários</h1>
<p>Visualizar e gerenciar usuários do sistema.</p>

<?php
if (isset($_SESSION['mensagem'])) {
    echo '<div class="alert alert-' . $_SESSION['mensagem']['tipo'] . '">' . htmlspecialchars($_SESSION['mensagem']['texto']) . '</div>';
    unset($_SESSION['mensagem']);
}
?>

<div class="card bg-light">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Tipo</th><th class="text-end">Ações</th></tr>
            </thead>
            <tbody>
                <?php
                $res = $conn->query("SELECT id, nome, email, tipo FROM usuarios ORDER BY FIELD(tipo, 'admin', 'vendedor', 'normal'), nome ASC");
                while ($u = $res->fetch_assoc()):
                ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['nome']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($u['tipo'])) ?></td>
                        <td class="text-end">
                            <?php if ($u['id'] === $_SESSION['usuario_id']): ?>
                                <span class="badge bg-secondary">Você (<?= htmlspecialchars(ucfirst($_SESSION['usuario_tipo'])) ?>)</span>

                            <?php elseif ($u['tipo'] === 'normal'): ?>
                                <form method="post" class="d-inline"><input type="hidden" name="promote_id" value="<?= $u['id'] ?>"><button type="submit" class="btn btn-sm btn-success">Promover a Vendedor</button></form>
                                <form method="post" class="d-inline" onsubmit="return confirm('Tem certeza?');"><input type="hidden" name="delete_id" value="<?= $u['id'] ?>"><button type="submit" class="btn btn-sm btn-danger">Excluir</button></form>

                            <?php elseif ($u['tipo'] === 'vendedor'): ?>
                                <?php if (ehAdmin()): // Apenas o Admin (nível dono) pode gerenciar vendedores ?>
                                    <form method="post" class="d-inline"><input type="hidden" name="demote_id" value="<?= $u['id'] ?>"><button type="submit" class="btn btn-sm btn-warning">Rebaixar</button></form>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Tem certeza?');"><input type="hidden" name="delete_id" value="<?= $u['id'] ?>"><button type="submit" class="btn btn-sm btn-danger">Excluir</button></form>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark">Vendedor</span>
                                <?php endif; ?>

                            <?php elseif ($u['tipo'] === 'admin'): ?>
                                <span class="badge bg-primary">Admin</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once(BASE_PATH . '/includes/footer.php');
?>