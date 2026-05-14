<?php
/**
 * Páginas PHP internas: exige sessão ativa; redireciona para o login.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario_logado'])) {
    require_once __DIR__ . '/caminhos_da_aplicacao.php';
    header('Location: ' . url_da_aplicacao('index.php'));
    exit;
}
