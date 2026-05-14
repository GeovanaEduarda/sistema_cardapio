<?php
/**
 * Sessão de caixa aberta: consulta à tabela sessoes_do_caixa.
 * Retorna null se a tabela não existir ou em caso de erro.
 */
if (!function_exists('obter_id_sessao_caixa_aberta')) {
    function obter_id_sessao_caixa_aberta(PDO $conexao_pdo): ?int
    {
        try {
            $consulta = $conexao_pdo->query(
                'SELECT id_sessao FROM sessoes_do_caixa WHERE fechado_em IS NULL ORDER BY id_sessao DESC LIMIT 1'
            );
            $valor = $consulta->fetchColumn();

            return $valor !== false ? (int) $valor : null;
        } catch (Throwable $e) {
            return null;
        }
    }
}

/**
 * Indica se a coluna de vínculo com sessão existe em movimentacoes_do_caixa (Etapa 3).
 */
if (!function_exists('movimentacoes_do_caixa_possui_coluna_sessao')) {
    function movimentacoes_do_caixa_possui_coluna_sessao(PDO $conexao_pdo): bool
    {
        try {
            $nome_banco = $conexao_pdo->query('SELECT DATABASE()')->fetchColumn();
            $consulta = $conexao_pdo->prepare(
                'SELECT COUNT(*) FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?'
            );
            $consulta->execute([$nome_banco, 'movimentacoes_do_caixa', 'id_sessao_do_caixa']);

            return (int) $consulta->fetchColumn() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }
}
