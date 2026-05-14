<?php
/**
 * Funções compartilhadas para pedidos (totais).
 */
if (!function_exists('recalcular_total_pedido')) {
    function recalcular_total_pedido(PDO $conexao_pdo, int $pedido_id): void
    {
        $st = $conexao_pdo->prepare('SELECT taxa_entrega, desconto FROM pedidos WHERE id_pedido = ?');
        $st->execute([$pedido_id]);
        $p = $st->fetch(PDO::FETCH_ASSOC);
        if (!$p) {
            return;
        }

        $st = $conexao_pdo->prepare('SELECT COALESCE(SUM(subtotal), 0) FROM itens_do_pedido WHERE id_pedido = ?');
        $st->execute([$pedido_id]);
        $soma_itens = (float) $st->fetchColumn();

        $st = $conexao_pdo->prepare(
            'SELECT COALESCE(SUM(ipa.subtotal), 0) FROM adicionais_do_item_do_pedido ipa
             INNER JOIN itens_do_pedido ip ON ip.id_item_pedido = ipa.id_item_pedido
             WHERE ip.id_pedido = ?'
        );
        $st->execute([$pedido_id]);
        $soma_adic = (float) $st->fetchColumn();

        $taxa = (float) $p['taxa_entrega'];
        $desc = (float) $p['desconto'];
        $total = max(0, $soma_itens + $soma_adic + $taxa - $desc);

        $up = $conexao_pdo->prepare('UPDATE pedidos SET valor_total = ? WHERE id_pedido = ?');
        $up->execute([$total, $pedido_id]);
    }
}
