<?php
/**
 * Relatórios e agregados financeiros (gráficos, lucro, faturamento).
 * Contrato: { success, message, dados }.
 */
require_once dirname(__DIR__) . '/configuracao/sessao_api.php';
require_once dirname(__DIR__) . '/configuracao/conexao_banco.php';
require_once dirname(__DIR__) . '/configuracao/resposta_json.php';
require_once dirname(__DIR__) . '/configuracao/auxiliar_financeiro.php';

$acao = $_GET['acao'] ?? '';
$metodo = $_SERVER['REQUEST_METHOD'];

try {
    if ($metodo !== 'GET') {
        enviar_resposta_json(resposta_json(false, 'Método não permitido.', ['metodo' => $metodo]), 405);
    }

    if ($acao === 'lucro_dia') {
        $data = $_GET['data'] ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            $data = date('Y-m-d');
        }
        $agg = financeiro_agregar_periodo($conexao_pdo, $data, $data);
        enviar_resposta_json(resposta_json(true, 'Lucro do dia calculado.', array_merge($agg, [
            'data' => $data,
        ])));
    }

    if ($acao === 'resumo_mes') {
        $ano = (int) ($_GET['ano'] ?? date('Y'));
        $mes = (int) ($_GET['mes'] ?? date('n'));
        if ($mes < 1 || $mes > 12) {
            $mes = (int) date('n');
        }
        $inicio = sprintf('%04d-%02d-01', $ano, $mes);
        $fim = date('Y-m-t', strtotime($inicio));
        $agg = financeiro_agregar_periodo($conexao_pdo, $inicio, $fim);
        enviar_resposta_json(resposta_json(true, 'Resumo do mês.', array_merge($agg, [
            'ano' => $ano,
            'mes' => $mes,
            'periodo_inicio' => $inicio,
            'periodo_fim' => $fim,
        ])));
    }

    if ($acao === 'vendas_ultimos_7_dias') {
        $dias = [];
        for ($i = 6; $i >= 0; --$i) {
            $data = date('Y-m-d', strtotime('-' . $i . ' days'));
            $agg = financeiro_agregar_periodo($conexao_pdo, $data, $data);
            $dias[] = [
                'data' => $data,
                'label' => date('d/m', strtotime($data)),
                'vendas' => round($agg['faturamento'], 2),
            ];
        }
        enviar_resposta_json(resposta_json(true, 'Vendas por dia (últimos 7 dias).', [
            'dias' => $dias,
        ]));
    }

    if ($acao === 'grafico_mensal') {
        $ano = (int) ($_GET['ano'] ?? date('Y'));
        if ($ano < 2000 || $ano > 2100) {
            $ano = (int) date('Y');
        }
        $meses = [];
        for ($m = 1; $m <= 12; ++$m) {
            $inicio = sprintf('%04d-%02d-01', $ano, $m);
            $fim = date('Y-m-t', strtotime($inicio));
            $agg = financeiro_agregar_periodo($conexao_pdo, $inicio, $fim);
            $meses[] = [
                'mes' => $m,
                'label' => ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'][$m],
                'faturamento' => round($agg['faturamento'], 2),
                'despesas' => round($agg['despesas'], 2),
                'saidas_caixa' => round($agg['saidas_caixa'], 2),
                'entradas_totais' => round($agg['entradas_totais'], 2),
                'lucro' => round($agg['lucro'], 2),
            ];
        }
        enviar_resposta_json(resposta_json(true, 'Série mensal.', [
            'ano' => $ano,
            'meses' => $meses,
        ]));
    }

    if ($acao === 'resumo_financeiro') {
        $hoje = date('Y-m-d');
        $dia = financeiro_agregar_periodo($conexao_pdo, $hoje, $hoje);
        $ano = (int) date('Y');
        $mes = (int) date('n');
        $inicio = sprintf('%04d-%02d-01', $ano, $mes);
        $fim = date('Y-m-t', strtotime($inicio));
        $mes_agg = financeiro_agregar_periodo($conexao_pdo, $inicio, $fim);
        enviar_resposta_json(resposta_json(true, 'Resumo consolidado.', [
            'hoje' => array_merge($dia, ['data' => $hoje]),
            'mes_atual' => array_merge($mes_agg, [
                'ano' => $ano,
                'mes' => $mes,
                'periodo_inicio' => $inicio,
                'periodo_fim' => $fim,
            ]),
        ]));
    }

    enviar_resposta_json(resposta_json(false, 'Ação inválida.', ['acao' => $acao]), 400);
} catch (Throwable $e) {
    enviar_resposta_json(resposta_json(false, 'Erro: ' . $e->getMessage(), new stdClass()), 500);
}
