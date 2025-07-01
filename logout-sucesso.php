<?php
// Incluímos o config e o header para manter o layout da página
require_once(__DIR__ . '/includes/config.php');
require_once(BASE_PATH . '/includes/header.php');

// A linha abaixo fará com que a página seja redirecionada para o index.php após 4 segundos.
// O 'content="4; ..."' significa "espere 4 segundos e então vá para a URL".
header("Refresh: 4; url=" . BASE_URL . "/index.php");
?>

<div class="row justify-content-center">
    <div class="col-md-6 text-center">
        <div class="alert alert-success mt-5">
            <h4 class="alert-heading">Saída Realizada com Sucesso!</h4>
            <p>Você foi desconectado da sua conta.</p>
            <hr>
            <p class="mb-0">Você será redirecionado para a página inicial em alguns segundos.</p>
        </div>
        
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary mt-3">
            Voltar para a Página Inicial Agora
        </a>
    </div>
</div>

<?php
// Inclui o footer para manter o layout da página
require_once(BASE_PATH . '/includes/footer.php');
?>