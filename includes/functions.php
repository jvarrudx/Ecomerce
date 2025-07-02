<?php
// includes/functions.php

/**
 * Verifica se o usuário logado é um Vendedor OU um Admin (nível dono).
 * Usado para dar acesso geral às páginas de gerenciamento.
 * @return bool
 */
function ehVendedor() {
    if (!isset($_SESSION['usuario_tipo'])) {
        return false;
    }
    // O 'admin' tem todas as permissões de um 'vendedor'
    return in_array($_SESSION['usuario_tipo'], ['vendedor', 'admin']);
}

/**
 * Verifica se o usuário logado é especificamente o Admin (nível dono).
 * Usado para ações exclusivas, como gerenciar outros vendedores.
 * @return bool
 */
function ehAdmin() {
    if (!isset($_SESSION['usuario_tipo'])) {
        return false;
    }
    return $_SESSION['usuario_tipo'] === 'admin';
}