<?php
/**
 * Autenticação por login (e-mail ou identificador) e senha.
 * Usa password_verify; suporta migração automática de senha em texto plano legada.
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/configuracao/conexao_banco.php';
require_once dirname(__DIR__) . '/configuracao/resposta_json.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $corpo = file_get_contents('php://input');
    $data = json_decode($corpo, true);

    if (!is_array($data)) {
        enviar_resposta_json(resposta_json(false, 'JSON inválido.', new stdClass()), 400);
    }

    $login = trim((string) ($data['login'] ?? ''));
    /* Não usar empty($data['senha']): em PHP, empty("0") é true. */
    $senha_digitada = (string) ($data['senha'] ?? '');

    if ($login === '' || $senha_digitada === '') {
        enviar_resposta_json(resposta_json(false, 'Preencha login e senha.', new stdClass()), 400);
    }

    /*
     * bindParam() liga por referência e, com PDO::ATTR_EMULATE_PREPARES => false,
     * pode gerar comportamento inesperado. bindValue/execute com array é o padrão seguro.
     */
    $sql = 'SELECT id_usuario, nome, login, senha, nivel, ativo, url_imagem
            FROM usuarios_administradores
            WHERE login = :login
            LIMIT 1';
    $stmt = $conexao_pdo->prepare($sql);
    $stmt->execute([':login' => $login]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        enviar_resposta_json(resposta_json(false, 'Usuário não encontrado.', new stdClass()), 401);
    }

    if ((int) $usuario['ativo'] === 0) {
        enviar_resposta_json(resposta_json(false, 'Sua conta está desativada. Fale com um admin.', new stdClass()), 403);
    }

    $hash_armazenado = trim((string) ($usuario['senha'] ?? ''));

    $senha_ok = false;
    if ($hash_armazenado !== '') {
        $info_hash = password_get_info($hash_armazenado);
        if ($info_hash['algo'] !== 0) {
            /* Hash gerado por password_hash (bcrypt, Argon2, etc.) */
            $senha_ok = password_verify($senha_digitada, $hash_armazenado);
        } else {
            /*
             * Legado: valor que não é hash PHP (ex.: texto plano antigo).
             * Compara com hash_equals e grava password_hash na primeira entrada ok.
             */
            $senha_ok = hash_equals($hash_armazenado, $senha_digitada);
            if ($senha_ok) {
                $novo = password_hash($senha_digitada, PASSWORD_DEFAULT);
                $upLegado = $conexao_pdo->prepare('UPDATE usuarios_administradores SET senha = ? WHERE id_usuario = ?');
                $upLegado->execute([$novo, $usuario['id_usuario']]);
                $hash_armazenado = $novo;
            }
        }
    }

    if (!$senha_ok) {
        enviar_resposta_json(resposta_json(false, 'Senha incorreta.', new stdClass()), 401);
    }

    if (password_get_info($hash_armazenado)['algo'] !== 0 && password_needs_rehash($hash_armazenado, PASSWORD_DEFAULT)) {
        $novo_hash = password_hash($senha_digitada, PASSWORD_DEFAULT);
        $atual = $conexao_pdo->prepare('UPDATE usuarios_administradores SET senha = ? WHERE id_usuario = ?');
        $atual->execute([$novo_hash, $usuario['id_usuario']]);
    }

    $_SESSION['id_usuario_logado'] = $usuario['id_usuario'];
    $_SESSION['nome_usuario_logado'] = $usuario['nome'];
    $_SESSION['nivel_usuario_logado'] = $usuario['nivel'];
    $_SESSION['login_usuario_logado'] = $usuario['login'];
    $_SESSION['ativo_usuario_logado'] = $usuario['ativo'];
    $_SESSION['url_imagem_usuario_logado'] = $usuario['url_imagem'];

    enviar_resposta_json(resposta_json(true, 'Login realizado com sucesso.', [
        'nivel' => $usuario['nivel'],
        'usuario' => [
            'id' => (int) $usuario['id_usuario'],
            'nome' => $usuario['nome'],
            'login' => $usuario['login'],
            'nivel' => $usuario['nivel'],
        ],
    ]));
} catch (Throwable $e) {
    enviar_resposta_json(resposta_json(false, 'Erro no servidor ao autenticar.', new stdClass()), 500);
}
