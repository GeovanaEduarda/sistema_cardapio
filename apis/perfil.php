<?php
/**
 * Perfil do usuário logado (dados) e alteração de senha via JSON.
 * Contrato: { success, message, dados }.
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/configuracao/sessao_api.php';
require_once dirname(__DIR__) . '/configuracao/conexao_banco.php';
require_once dirname(__DIR__) . '/configuracao/caminhos_da_aplicacao.php';
require_once dirname(__DIR__) . '/configuracao/resposta_json.php';

$acao = $_GET['acao'] ?? '';
$metodo = $_SERVER['REQUEST_METHOD'];

try {
    $uid = (int) ($_SESSION['id_usuario_logado'] ?? 0);

    if ($metodo === 'GET' && $acao === 'dados') {
        $st = $conexao_pdo->prepare(
            'SELECT id_usuario, nome, login, nivel, ativo, url_imagem FROM usuarios_administradores WHERE id_usuario = ? LIMIT 1'
        );
        $st->execute([$uid]);
        $u = $st->fetch(PDO::FETCH_ASSOC);
        if (!$u) {
            enviar_resposta_json(resposta_json(false, 'Usuário não encontrado.', new stdClass()), 404);
        }
        $u['url_imagem'] = url_publica_imagem($u['url_imagem'] ?? '');
        enviar_resposta_json(resposta_json(true, 'Perfil carregado.', ['usuario' => $u]));
    }

    if ($metodo === 'POST' && $acao === 'alterar_senha') {
        $j = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($j)) {
            enviar_resposta_json(resposta_json(false, 'JSON inválido.', new stdClass()), 400);
        }
        $atual = (string) ($j['senha_atual'] ?? '');
        $nova = (string) ($j['nova_senha'] ?? '');
        $conf = (string) ($j['confirmar_senha'] ?? '');
        if ($nova === '' || strlen($nova) < 4) {
            enviar_resposta_json(resposta_json(false, 'Nova senha muito curta (mín. 4 caracteres).', new stdClass()), 400);
        }
        if ($nova !== $conf) {
            enviar_resposta_json(resposta_json(false, 'Confirmação da nova senha não confere.', new stdClass()), 400);
        }

        $st = $conexao_pdo->prepare('SELECT senha FROM usuarios_administradores WHERE id_usuario = ? LIMIT 1');
        $st->execute([$uid]);
        $linha = $st->fetch(PDO::FETCH_ASSOC);
        if (!$linha || !password_verify($atual, (string) $linha['senha'])) {
            enviar_resposta_json(resposta_json(false, 'Senha atual incorreta.', new stdClass()), 403);
        }

        $hash = password_hash($nova, PASSWORD_DEFAULT);
        $up = $conexao_pdo->prepare('UPDATE usuarios_administradores SET senha = ? WHERE id_usuario = ?');
        $up->execute([$hash, $uid]);

        enviar_resposta_json(resposta_json(true, 'Senha alterada com sucesso.', new stdClass()));
    }

    enviar_resposta_json(resposta_json(false, 'Ação ou método inválido.', ['acao' => $acao, 'metodo' => $metodo]), 400);
} catch (Throwable $e) {
    enviar_resposta_json(resposta_json(false, 'Erro no servidor.', new stdClass()), 500);
}
