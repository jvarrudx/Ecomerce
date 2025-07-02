<?php
require_once(__DIR__ . '/includes/config.php');
require_once(__DIR__ . '/includes/db.php');

$erro = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['email']) || empty($_POST['senha'])) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();

        if ($user && password_verify($senha, $user['senha'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_tipo'] = $user['tipo'];
            $_SESSION['usuario_nome'] = $user['nome'];

            if (!empty($_POST['redirect_url'])) {
                header('Location: ' . $_POST['redirect_url']);
            } else {
                header('Location: ' . BASE_URL . '/index.php');
            }
            exit;
        } else {
            $erro = 'E-mail ou senha inválidos.';
        }
    }
}

require_once(BASE_PATH . '/includes/header.php');
?>

<div class="row justify-content-center">
    <div class="col-md-6">

        <h1 class="text-center mb-4">Login</h1>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form class="login-form" method="post" action="<?= BASE_URL ?>/login.php<?= isset($_GET['redirect_url']) ? '?redirect_url=' . htmlspecialchars($_GET['redirect_url']) : '' ?>">
            <?php if(isset($_GET['redirect_url'])): ?>
                <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($_GET['redirect_url']) ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" name="senha" id="senha" class="form-control" required>
            </div>
            <div class="d-grid mt-4">
                <button class="btn btn-primary" type="submit">Entrar</button>
            </div>
        </form>

    </div>
</div>

<?php require_once(BASE_PATH . '/includes/footer.php'); ?>