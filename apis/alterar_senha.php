<?php
/**
 * Alteração de senha (formulário POST do perfil). Exige sessão e password_hash no banco.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/configuracao/caminhos_da_aplicacao.php';
require_once dirname(__DIR__) . '/configuracao/conexao_banco.php';

if (empty($_SESSION['id_usuario_logado'])) {
    header('Location: ' . url_da_aplicacao('index.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url_da_aplicacao('publico/perfil/perfil.php'));
    exit;
}

$senha_atual = $_POST['senha_atual'] ?? '';
$nova_senha = $_POST['nova_senha'] ?? '';
$confirmar = $_POST['confirmar_senha'] ?? '';

if ($nova_senha !== $confirmar || strlen($nova_senha) < 4) {
    header('Location: ' . url_da_aplicacao('publico/perfil/perfil.php') . '?senha=erro');
    exit;
}

try {
    $stmt = $conexao_pdo->prepare('SELECT senha FROM usuarios_administradores WHERE id_usuario = ? LIMIT 1');
    $stmt->execute([(int) $_SESSION['id_usuario_logado']]);
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$linha || !password_verify($senha_atual, $linha['senha'])) {
        header('Location: ' . url_da_aplicacao('publico/perfil/perfil.php') . '?senha=erro_atual');
        exit;
    }

    $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    $atualizar = $conexao_pdo->prepare('UPDATE usuarios_administradores SET senha = ? WHERE id_usuario = ?');
    $atualizar->execute([$hash, (int) $_SESSION['id_usuario_logado']]);

    header('Location: ' . url_da_aplicacao('publico/perfil/perfil.php') . '?senha=ok');
    exit;
} catch (PDOException $e) {
    header('Location: ' . url_da_aplicacao('publico/perfil/perfil.php') . '?senha=erro');
    exit;
}
