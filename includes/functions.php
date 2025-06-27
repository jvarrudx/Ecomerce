<?php
// includes/functions.php

/**
 * Verifica se o usuário logado na sessão atual é um administrador.
 *
 * @return bool Retorna true se o usuário for admin, false caso contrário.
 */
function ehAdmin() {
    // A sessão já deve ter sido iniciada pelo config.php
    return isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin';
}

// Você pode adicionar outras funções úteis aqui no futuro.
// Exemplo:
// function estaLogado() {
//     return isset($_SESSION['usuario_id']);
// }
?>