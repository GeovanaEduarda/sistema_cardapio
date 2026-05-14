<?php
/**
 * Abertura / fechamento de sessão de caixa e status.
 * Contrato: { success, message, dados }.
 */
require_once dirname(__DIR__) . '/configuracao/sessao_api.php';
require_once dirname(__DIR__) . '/configuracao/conexao_banco.php';
require_once dirname(__DIR__) . '/configuracao/resposta_json.php';
require_once dirname(__DIR__) . '/configuracao/auxiliar_caixa.php';

$acao = $_GET['acao'] ?? '';
$metodo = $_SERVER['REQUEST_METHOD'];

/**
 * Saldo da sessão: abertura informada + entradas − saídas (apenas mov vinculadas à sessão).
 */
function saldo_sessao(PDO $conexao_pdo, int $id_sessao, float $abertura): float
{
    if (!movimentacoes_do_caixa_possui_coluna_sessao($conexao_pdo)) {
        return $abertura;
    }
    $st = $conexao_pdo->prepare(
        "SELECT COALESCE(SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END), 0)
              - COALESCE(SUM(CASE WHEN tipo = 'saida' THEN valor ELSE 0 END), 0)
         FROM movimentacoes_do_caixa WHERE id_sessao_do_caixa = ?"
    );
    $st->execute([$id_sessao]);
    $mov = (float) $st->fetchColumn();

    return round($abertura + $mov, 2);
}

try {
    if ($metodo === 'GET' && $acao === 'status') {
        try {
            $st = $conexao_pdo->query(
                'SELECT id_sessao, id_usuario, aberto_em, fechado_em, saldo_informado_abertura,
                        saldo_informado_fechamento, observacao_abertura
                 FROM sessoes_do_caixa WHERE fechado_em IS NULL ORDER BY id_sessao DESC LIMIT 1'
            );
            $aberta = $st->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $aberta = false;
        }

        $saldo_global = null;
        try {
            $e = (float) $conexao_pdo->query("SELECT COALESCE(SUM(valor),0) FROM movimentacoes_do_caixa WHERE tipo='entrada'")->fetchColumn();
            $s = (float) $conexao_pdo->query("SELECT COALESCE(SUM(valor),0) FROM movimentacoes_do_caixa WHERE tipo='saida'")->fetchColumn();
            $saldo_global = round($e - $s, 2);
        } catch (Throwable $e) {
            $saldo_global = null;
        }

        $saldo_sessao_calc = null;
        if ($aberta) {
            $saldo_sessao_calc = saldo_sessao(
                $conexao_pdo,
                (int) $aberta['id_sessao'],
                (float) $aberta['saldo_informado_abertura']
            );
        }

        enviar_resposta_json(resposta_json(true, 'Status do caixa.', [
            'sessao_aberta' => $aberta ?: null,
            'saldo_estimado_sessao' => $saldo_sessao_calc,
            'saldo_movimentacoes_global' => $saldo_global,
        ]));
    }

    if ($metodo === 'GET' && $acao === 'historico') {
        try {
            $lim = min(100, max(1, (int) ($_GET['limite'] ?? 30)));
            $st = $conexao_pdo->query(
                "SELECT id_sessao, id_usuario, aberto_em, fechado_em, saldo_informado_abertura,
                        saldo_informado_fechamento, observacao_abertura, observacao_fechamento
                 FROM sessoes_do_caixa ORDER BY id_sessao DESC LIMIT {$lim}"
            );
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $rows = [];
        }
        enviar_resposta_json(resposta_json(true, 'Histórico de sessões.', ['sessoes' => $rows]));
    }

    if ($metodo !== 'POST') {
        enviar_resposta_json(resposta_json(false, 'Método não permitido.', ['metodo' => $metodo]), 405);
    }

    $j = json_decode(file_get_contents('php://input'), true);
    if (!is_array($j)) {
        enviar_resposta_json(resposta_json(false, 'JSON inválido.', new stdClass()), 400);
    }

    $uid = (int) ($_SESSION['id_usuario_logado'] ?? 0) ?: null;

    if ($acao === 'abrir') {
        if (obter_id_sessao_caixa_aberta($conexao_pdo) !== null) {
            enviar_resposta_json(resposta_json(false, 'Já existe uma sessão de caixa aberta.', new stdClass()), 400);
        }
        $saldo = (float) ($j['saldo_informado_abertura'] ?? 0);
        $obs = isset($j['observacao']) ? substr((string) $j['observacao'], 0, 500) : null;
        $ins = $conexao_pdo->prepare(
            'INSERT INTO sessoes_do_caixa (id_usuario, saldo_informado_abertura, observacao_abertura)
             VALUES (?,?,?)'
        );
        $ins->execute([$uid, $saldo, $obs]);
        enviar_resposta_json(resposta_json(true, 'Caixa aberto.', ['id_sessao' => (int) $conexao_pdo->lastInsertId()]));
    }

    if ($acao === 'fechar') {
        $sid = obter_id_sessao_caixa_aberta($conexao_pdo);
        if ($sid === null) {
            enviar_resposta_json(resposta_json(false, 'Nenhuma sessão aberta.', new stdClass()), 400);
        }
        $st = $conexao_pdo->prepare('SELECT saldo_informado_abertura FROM sessoes_do_caixa WHERE id_sessao = ?');
        $st->execute([$sid]);
        $ab = (float) $st->fetchColumn();
        $calc = saldo_sessao($conexao_pdo, $sid, $ab);
        $obs = isset($j['observacao']) ? substr((string) $j['observacao'], 0, 500) : null;
        $up = $conexao_pdo->prepare(
            'UPDATE sessoes_do_caixa SET fechado_em = NOW(), saldo_informado_fechamento = ?, observacao_fechamento = ?
           WHERE id_sessao = ? AND fechado_em IS NULL'
        );
        $up->execute([$calc, $obs, $sid]);
        enviar_resposta_json(resposta_json(true, 'Caixa fechado.', [
            'id_sessao' => $sid,
            'saldo_calculado' => $calc,
        ]));
    }

    enviar_resposta_json(resposta_json(false, 'Ação inválida.', ['acao' => $acao]), 400);
} catch (Throwable $e) {
    enviar_resposta_json(resposta_json(false, 'Erro: ' . $e->getMessage(), new stdClass()), 500);
}
