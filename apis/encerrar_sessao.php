<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/configuracao/caminhos_da_aplicacao.php';
require_once dirname(__DIR__) . '/configuracao/resposta_json.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$querJson = (isset($_GET['formato']) && $_GET['formato'] === 'json')
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos((string) $_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

if ($querJson) {
    enviar_resposta_json(resposta_json(true, 'Sessão encerrada.', new stdClass()));
}

header('Location: ' . url_da_aplicacao('index.php'));
exit;