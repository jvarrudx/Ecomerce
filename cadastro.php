<?php
// 1. Inclui os arquivos de configuração e banco de dados
require_once(__DIR__ . '/includes/config.php');
require_once(__DIR__ . '/includes/db.php');

// Variáveis para armazenar mensagens de sucesso ou erro
$sucesso = '';
$erro = '';
$isAdmin = isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin';

// 2. Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 3. Validação dos dados do lado do servidor
    if (empty($_POST['nome']) || empty($_POST['email']) || empty($_POST['senha'])) {
        $erro = "Todos os campos são obrigatórios.";
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $erro = "O formato do e-mail é inválido.";
    } elseif (strlen($_POST['senha']) < 6) {
        $erro = "A senha deve ter pelo menos 6 caracteres.";
    } else {
        $email = $_POST['email'];

        // 4. VERIFICA SE O E-MAIL JÁ EXISTE (Evita Duplicidade)
        $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $resultado_check = $stmt_check->get_result();

        if ($resultado_check->num_rows > 0) {
            $erro = "Este e-mail já está cadastrado. Por favor, tente outro.";
        } else {
            // Se o e-mail não existe, prossegue com o cadastro
            $nome = $_POST['nome'];
            $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Senha segura
            $tipo = ($isAdmin && isset($_POST['tipo'])) ? $_POST['tipo'] : 'normal';

            // 5. SQL Injection CORRIGIDO com Prepared Statements
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nome, $email, $senha_hash, $tipo);

            if ($stmt->execute()) {
                $sucesso = 'Cadastro realizado com sucesso! Você já pode fazer o <a href="' . BASE_URL . '/login.php">login</a>.';
            } else {
                $erro = "Ocorreu um erro inesperado ao realizar o cadastro. Tente novamente.";
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
}

// 6. Inclui o header APÓS toda a lógica PHP
require_once(BASE_PATH . '/includes/header.php');
?>

<h1>Cadastro de Usuário</h1>

<?php
// 7. Exibe as mensagens de sucesso ou erro dentro do layout
if (!empty($sucesso)) :
?>
    <div class="alert alert-success" role="alert">
        <?= $sucesso // A mensagem já inclui um link, então não usamos htmlspecialchars aqui ?>
    </div>
<?php endif; ?>
<?php if (!empty($erro)) : ?>
    <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($erro) ?>
    </div>
<?php endif; ?>

<form method="post" action="<?= BASE_URL ?>/cadastro.php">
    <div class="mb-3">
        <label for="nome" class="form-label">Nome</label>
        <input type="text" name="nome" id="nome" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">E-mail</label>
        <input type="email" name="email" id="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="senha" class="form-label">Senha</label>
        <input type="password" name="senha" id="senha" class="form-control" required>
    </div>

    <?php if ($isAdmin) : ?>
    <div class="mb-3">
        <label for="tipo" class="form-label">Tipo de Usuário</label>
        <select name="tipo" id="tipo" class="form-select">
            <option value="normal">Normal</option>
            <option value="admin">Administrador</option>
        </select>
    </div>
    <?php endif; ?>
    
    <button class="btn btn-primary" type="submit">Cadastrar</button>
</form>

<?php
// 9. Inclui o footer
require_once(BASE_PATH . '/includes/footer.php');
?>