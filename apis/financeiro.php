<?php
/**
 * Financeiro: despesas, resumos (usa o mesmo motor que relatorios.php).
 * Contrato: { success, message, dados }.
 */
require_once dirname(__DIR__) . '/configuracao/sessao_api.php';
require_once dirname(__DIR__) . '/configuracao/conexao_banco.php';
require_once dirname(__DIR__) . '/configuracao/resposta_json.php';
require_once dirname(__DIR__) . '/configuracao/auxiliar_financeiro.php';

$acao = $_GET['acao'] ?? '';
$metodo = $_SERVER['REQUEST_METHOD'];

/**
 * Normaliza tipo de despesa vindos do front (Fixa / fixa / variável).
 */
function financeiro_normalizar_tipo_despesa($raw): string
{
    $s = strtolower(trim((string) $raw));
    if ($s === 'fixa' || $s === 'fixo' || $s === 'f') {
        return 'fixa';
    }

    return 'variavel';
}

try {
    if ($metodo === 'GET' && $acao === 'listar_despesas') {
        $st = $conexao_pdo->query(
            'SELECT id_despesa, fornecedor, valor, data_lancamento, tipo, observacao, ativo, criado_em
             FROM despesas WHERE ativo = 1 ORDER BY data_lancamento DESC, id_despesa DESC LIMIT 500'
        );
        enviar_resposta_json(resposta_json(true, 'Despesas carregadas.', ['despesas' => $st->fetchAll(PDO::FETCH_ASSOC)]));
    }

    if ($metodo === 'GET' && $acao === 'listar_movimentacoes') {
        $st = $conexao_pdo->query(
            'SELECT id_movimentacao, tipo, origem, valor, descricao, id_pedido, id_despesa, criado_em
             FROM movimentacoes_do_caixa ORDER BY criado_em DESC LIMIT 500'
        );
        enviar_resposta_json(resposta_json(true, 'Movimentações carregadas.', ['movimentacoes' => $st->fetchAll(PDO::FETCH_ASSOC)]));
    }

    if ($metodo === 'GET' && $acao === 'resumo_mes') {
        $ano = (int) ($_GET['ano'] ?? date('Y'));
        $mes = (int) ($_GET['mes'] ?? date('n'));
        if ($mes < 1 || $mes > 12) {
            $mes = (int) date('n');
        }
        $inicio = sprintf('%04d-%02d-01', $ano, $mes);
        $fim = date('Y-m-t', strtotime($inicio));
        $agg = financeiro_agregar_periodo($conexao_pdo, $inicio, $fim);
        enviar_resposta_json(resposta_json(true, 'Resumo calculado.', array_merge($agg, [
            'ano' => $ano,
            'mes' => $mes,
            'periodo_inicio' => $inicio,
            'periodo_fim' => $fim,
            'entradas_caixa' => $agg['entradas_totais'],
            'lucro_estimado' => $agg['lucro'],
        ])));
    }

    if ($metodo === 'GET' && $acao === 'lucro_dia') {
        $data = $_GET['data'] ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            $data = date('Y-m-d');
        }
        $agg = financeiro_agregar_periodo($conexao_pdo, $data, $data);
        enviar_resposta_json(resposta_json(true, 'Lucro do dia.', array_merge($agg, ['data' => $data])));
    }

    if ($metodo === 'POST' && $acao === 'criar_despesa') {
        $j = json_decode(file_get_contents('php://input'), true);
        if (!is_array($j)) {
            enviar_resposta_json(resposta_json(false, 'JSON inválido.', new stdClass()), 400);
        }
        $fornecedor = substr((string) ($j['fornecedor'] ?? ''), 0, 160);
        $valor = (float) ($j['valor'] ?? 0);
        $data = (string) ($j['data_lancamento'] ?? date('Y-m-d'));
        $tipo = financeiro_normalizar_tipo_despesa($j['tipo'] ?? 'variavel');
        $obs = isset($j['observacao']) ? substr((string) $j['observacao'], 0, 2000) : null;
        $uid = (int) ($_SESSION['id_usuario_logado'] ?? 0) ?: null;

        if ($fornecedor === '' || $valor <= 0) {
            enviar_resposta_json(resposta_json(false, 'Fornecedor e valor obrigatórios.', new stdClass()), 400);
        }

        $ins = $conexao_pdo->prepare(
            'INSERT INTO despesas (fornecedor, valor, data_lancamento, tipo, observacao, id_usuario, ativo)
             VALUES (?,?,?,?,?, ?,1)'
        );
        $ins->execute([$fornecedor, $valor, $data, $tipo, $obs, $uid]);

        enviar_resposta_json(resposta_json(true, 'Despesa registrada.', ['id_despesa' => (int) $conexao_pdo->lastInsertId()]));
    }

    if ($metodo === 'POST' && $acao === 'desativar_despesa') {
        $j = json_decode(file_get_contents('php://input'), true);
        if (!is_array($j) || empty($j['id_despesa'])) {
            enviar_resposta_json(resposta_json(false, 'id_despesa obrigatório.', new stdClass()), 400);
        }
        $id = (int) $j['id_despesa'];
        $up = $conexao_pdo->prepare('UPDATE despesas SET ativo = 0 WHERE id_despesa = ?');
        $up->execute([$id]);
        enviar_resposta_json(resposta_json(true, 'Despesa desativada.', ['id_despesa' => $id]));
    }

    enviar_resposta_json(resposta_json(false, 'Ação ou método inválido.', ['acao' => $acao, 'metodo' => $metodo]), 400);
} catch (Throwable $e) {
    enviar_resposta_json(resposta_json(false, 'Erro: ' . $e->getMessage(), new stdClass()), 500);
}
