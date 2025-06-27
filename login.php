<?php
// 1. Inclui os arquivos de configuração e banco de dados de forma segura
require_once(__DIR__ . '/includes/config.php');
require_once(__DIR__ . '/includes/db.php');

// Variável para armazenar mensagens de erro
$erro = '';

// 2. Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validação básica
    if (empty($_POST['email']) || empty($_POST['senha'])) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        // 3. SQL Injection CORRIGIDO com Prepared Statements
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email); // 's' para string
        $stmt->execute();
        $resultado = $stmt->get_result();
        $user = $resultado->fetch_assoc();
        $stmt->close();

        // 4. Verifica a senha usando a função segura password_verify
        if ($user && password_verify($senha, $user['senha'])) {
            // Login bem-sucedido: armazena dados na sessão
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_tipo'] = $user['tipo'];
            $_SESSION['usuario_nome'] = $user['nome']; // Opcional, mas útil

            // 5. Redirecionamento CORRIGIDO com BASE_URL
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        } else {
            // Se o login falhar, define a mensagem de erro
            $erro = 'E-mail ou senha inválidos. Tente novamente.';
        }
    }
}

// 6. Inclui o header APÓS toda a lógica PHP
require_once(BASE_PATH . '/includes/header.php');
?>

<h1>Login</h1>

<?php
// 7. Exibe a mensagem de erro dentro do layout da página, se houver alguma
if (!empty($erro)) :
?>
    <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($erro) ?>
    </div>
<?php endif; ?>

<form method="post" action="<?= BASE_URL ?>/login.php">
    <div class="mb-3">
        <label for="email" class="form-label">E-mail</label>
        <input type="email" name="email" id="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="senha" class="form-label">Senha</label>
        <input type="password" name="senha" id="senha" class="form-control" required>
    </div>
    <button class="btn btn-primary" type="submit">Entrar</button>
</form>

<?php
// 9. Inclui o footer
require_once(BASE_PATH . '/includes/footer.php');
?>