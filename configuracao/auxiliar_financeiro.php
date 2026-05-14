<?php
/**
 * Agregados financeiros (faturamento de vendas, despesas, caixa, lucro).
 *
 * Definições:
 * - faturamento: soma de entradas em movimentacoes_do_caixa com origem = 'pedido' (vendas registradas).
 * - entradas_totais: todas as entradas no período (pedido + manual/suprimento).
 * - despesas: soma da tabela despesas (compromissos / custos registrados).
 * - saidas_caixa: saídas registradas em movimentacoes_do_caixa.
 * - lucro: entradas_totais - despesas - saidas_caixa.
 */
if (!function_exists('financeiro_agregar_periodo')) {
    /**
     * @return array{faturamento: float, despesas: float, saidas_caixa: float, entradas_totais: float, lucro: float}
     */
    function financeiro_agregar_periodo(PDO $conexao_pdo, string $inicio, string $fim): array
    {
        $st = $conexao_pdo->prepare(
            "SELECT COALESCE(SUM(valor), 0) FROM movimentacoes_do_caixa
             WHERE tipo = 'entrada' AND origem = 'pedido' AND DATE(criado_em) BETWEEN ? AND ?"
        );
        $st->execute([$inicio, $fim]);
        $faturamento = (float) $st->fetchColumn();

        $st = $conexao_pdo->prepare(
            'SELECT COALESCE(SUM(valor), 0) FROM despesas WHERE ativo = 1 AND data_lancamento BETWEEN ? AND ?'
        );
        $st->execute([$inicio, $fim]);
        $despesas = (float) $st->fetchColumn();

        $st = $conexao_pdo->prepare(
            "SELECT COALESCE(SUM(valor), 0) FROM movimentacoes_do_caixa
             WHERE tipo = 'saida' AND DATE(criado_em) BETWEEN ? AND ?"
        );
        $st->execute([$inicio, $fim]);
        $saidas = (float) $st->fetchColumn();

        $st = $conexao_pdo->prepare(
            "SELECT COALESCE(SUM(valor), 0) FROM movimentacoes_do_caixa
             WHERE tipo = 'entrada' AND DATE(criado_em) BETWEEN ? AND ?"
        );
        $st->execute([$inicio, $fim]);
        $entradas_totais = (float) $st->fetchColumn();

        $lucro = $entradas_totais - $despesas - $saidas;

        return [
            'faturamento' => $faturamento,
            'despesas' => $despesas,
            'saidas_caixa' => $saidas,
            'entradas_totais' => $entradas_totais,
            'lucro' => $lucro,
        ];
    }
}
