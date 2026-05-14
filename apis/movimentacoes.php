<?php
/**
 * Movimentações de caixa (listagem e lançamentos manuais).
 * Contrato: { success, message, dados }.
 */
require_once dirname(__DIR__) . '/configuracao/sessao_api.php';
require_once dirname(__DIR__) . '/configuracao/conexao_banco.php';
require_once dirname(__DIR__) . '/configuracao/resposta_json.php';
require_once dirname(__DIR__) . '/configuracao/auxiliar_caixa.php';

$acao = $_GET['acao'] ?? '';
$metodo = $_SERVER['REQUEST_METHOD'];

try {
    if ($metodo === 'GET' && $acao === 'listar') {
        $lim = min(500, max(1, (int) ($_GET['limite'] ?? 100)));
        $st = $conexao_pdo->query(
            'SELECT * FROM movimentacoes_do_caixa ORDER BY criado_em DESC LIMIT ' . $lim
        );
        enviar_resposta_json(resposta_json(true, 'Movimentações carregadas.', ['movimentacoes' => $st->fetchAll(PDO::FETCH_ASSOC)]));
    }

    if ($metodo !== 'POST') {
        enviar_resposta_json(resposta_json(false, 'Método não permitido.', ['metodo' => $metodo]), 405);
    }

    $j = json_decode(file_get_contents('php://input'), true);
    if (!is_array($j)) {
        enviar_resposta_json(resposta_json(false, 'JSON inválido.', new stdClass()), 400);
    }

    $uid = (int) ($_SESSION['id_usuario_logado'] ?? 0) ?: null;
    $sess = obter_id_sessao_caixa_aberta($conexao_pdo);
    $temSessCol = movimentacoes_do_caixa_possui_coluna_sessao($conexao_pdo);

    if ($acao === 'registrar_entrada') {
        $valor = (float) ($j['valor'] ?? 0);
        $desc = isset($j['descricao']) ? substr((string) $j['descricao'], 0, 255) : 'Entrada manual';
        $origem = substr(preg_replace('/[^a-z0-9_]/i', '', (string) ($j['origem'] ?? 'manual')), 0, 64) ?: 'manual';
        if ($valor <= 0) {
            enviar_resposta_json(resposta_json(false, 'Valor inválido.', new stdClass()), 400);
        }
        if ($temSessCol) {
            $ins = $conexao_pdo->prepare(
                'INSERT INTO movimentacoes_do_caixa (tipo, origem, valor, descricao, id_usuario, id_sessao_do_caixa)
                 VALUES (\'entrada\', ?, ?, ?, ?, ?)'
            );
            $ins->execute([$origem, $valor, $desc, $uid, $sess]);
        } else {
            $ins = $conexao_pdo->prepare(
                'INSERT INTO movimentacoes_do_caixa (tipo, origem, valor, descricao, id_usuario)
                 VALUES (\'entrada\', ?, ?, ?, ?)'
            );
            $ins->execute([$origem, $valor, $desc, $uid]);
        }
        enviar_resposta_json(resposta_json(true, 'Entrada registrada.', ['id_movimentacao' => (int) $conexao_pdo->lastInsertId()]));
    }

    if ($acao === 'registrar_saida') {
        $valor = (float) ($j['valor'] ?? 0);
        $desc = isset($j['descricao']) ? substr((string) $j['descricao'], 0, 255) : 'Saída manual';
        $origem = substr(preg_replace('/[^a-z0-9_]/i', '', (string) ($j['origem'] ?? 'sangria')), 0, 64) ?: 'sangria';
        if ($valor <= 0) {
            enviar_resposta_json(resposta_json(false, 'Valor inválido.', new stdClass()), 400);
        }
        if ($temSessCol) {
            $ins = $conexao_pdo->prepare(
                'INSERT INTO movimentacoes_do_caixa (tipo, origem, valor, descricao, id_usuario, id_sessao_do_caixa)
                 VALUES (\'saida\', ?, ?, ?, ?, ?)'
            );
            $ins->execute([$origem, $valor, $desc, $uid, $sess]);
        } else {
            $ins = $conexao_pdo->prepare(
                'INSERT INTO movimentacoes_do_caixa (tipo, origem, valor, descricao, id_usuario)
                 VALUES (\'saida\', ?, ?, ?, ?)'
            );
            $ins->execute([$origem, $valor, $desc, $uid]);
        }
        enviar_resposta_json(resposta_json(true, 'Saída registrada.', ['id_movimentacao' => (int) $conexao_pdo->lastInsertId()]));
    }

    enviar_resposta_json(resposta_json(false, 'Ação inválida.', ['acao' => $acao]), 400);
} catch (Throwable $e) {
    enviar_resposta_json(resposta_json(false, 'Erro: ' . $e->getMessage(), new stdClass()), 500);
}
