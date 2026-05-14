<?php
/**
 * API de pedidos: listar, criar, itens, adicionais, status, concluir, cancelar.
 * Contrato: { "success", "message", "dados" }.
 */
require_once dirname(__DIR__) . '/configuracao/sessao_api.php';
require_once dirname(__DIR__) . '/configuracao/conexao_banco.php';
require_once dirname(__DIR__) . '/configuracao/resposta_json.php';
require_once dirname(__DIR__) . '/configuracao/auxiliar_pedidos.php';
require_once dirname(__DIR__) . '/configuracao/auxiliar_caixa.php';

$acao = $_GET['acao'] ?? '';
$metodo = $_SERVER['REQUEST_METHOD'];

/**
 * Monta lista de pedidos com itens e adicionais para o painel.
 */
function montar_pedidos_listagem(PDO $conexao_pdo, bool $somente_ativos): array
{
    $sql = 'SELECT p.* FROM pedidos p';
    if ($somente_ativos) {
        $sql .= " WHERE p.status NOT IN ('concluido','cancelado')";
    }
    $sql .= ' ORDER BY p.id_pedido DESC LIMIT 200';

    $pedidos_rows = $conexao_pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $resultado = [];

    foreach ($pedidos_rows as $row) {
        $pid = (int) $row['id_pedido'];

        $stc = $conexao_pdo->prepare('SELECT nome, rua, numero, bairro FROM clientes WHERE id_cliente = ?');
        $stc->execute([$row['id_cliente']]);
        $cli = $stc->fetch(PDO::FETCH_ASSOC) ?: ['nome' => null, 'rua' => null, 'numero' => null, 'bairro' => null];

        $sti = $conexao_pdo->prepare(
            'SELECT ip.id_item_pedido, ip.quantidade, ip.preco_unitario, ip.subtotal, ic.nome AS nome_item
             FROM itens_do_pedido ip
             INNER JOIN itens_do_cardapio ic ON ic.id_item = ip.id_item
             WHERE ip.id_pedido = ?'
        );
        $sti->execute([$pid]);
        $linhas_itens = $sti->fetchAll(PDO::FETCH_ASSOC);

        $itens = [];
        foreach ($linhas_itens as $li) {
            $iid = (int) $li['id_item_pedido'];
            $sta = $conexao_pdo->prepare(
                'SELECT a.nome_adicional, ipa.quantidade, ipa.valor_unitario, ipa.subtotal
                 FROM adicionais_do_item_do_pedido ipa
                 INNER JOIN adicionais a ON a.id_adicional = ipa.id_adicional
                 WHERE ipa.id_item_pedido = ?'
            );
            $sta->execute([$iid]);
            $adis = $sta->fetchAll(PDO::FETCH_ASSOC);

            $itens[] = [
                'nome' => $li['nome_item'],
                'quantidade' => (int) $li['quantidade'],
                'preco_unitario' => (float) $li['preco_unitario'],
                'subtotal' => (float) $li['subtotal'],
                'adicionais' => array_map(static function ($a) {
                    return [
                        'nome' => $a['nome_adicional'],
                        'quantidade' => (int) $a['quantidade'],
                        'valor_unitario' => (float) $a['valor_unitario'],
                        'subtotal' => (float) $a['subtotal'],
                    ];
                }, $adis),
            ];
        }

        $resultado[] = [
            'id_pedido' => $pid,
            'cliente' => $cli['nome'],
            'mesa' => $row['identificador_mesa'],
            'origem' => $row['origem'],
            'valor_total' => number_format((float) $row['valor_total'], 2, ',', '.'),
            'valor_total_numero' => (float) $row['valor_total'],
            'status' => $row['status'],
            'data_pedido' => $row['data_pedido'],
            'rua' => $cli['rua'],
            'numero' => $cli['numero'],
            'bairro' => $cli['bairro'],
            'itens' => $itens,
        ];
    }

    return $resultado;
}

try {
    if ($metodo === 'GET' && $acao === 'listar') {
        $somente = ($_GET['somente_ativos'] ?? '1') === '1';
        $lista = montar_pedidos_listagem($conexao_pdo, $somente);
        enviar_resposta_json(resposta_json(true, 'Pedidos carregados.', ['pedidos' => $lista]));
    }

    if ($metodo === 'GET' && $acao === 'detalhe') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id < 1) {
            enviar_resposta_json(resposta_json(false, 'ID inválido.', ['pedido' => null]), 400);
        }
        $lista = montar_pedidos_listagem($conexao_pdo, false);
        foreach ($lista as $p) {
            if ((int) $p['id_pedido'] === $id) {
                enviar_resposta_json(resposta_json(true, 'OK', ['pedido' => $p]));
            }
        }
        enviar_resposta_json(resposta_json(false, 'Pedido não encontrado.', ['pedido' => null]), 404);
    }

    $input = file_get_contents('php://input');
    $json = json_decode($input, true);

    if ($metodo === 'POST' && $acao === 'criar') {
        if (!is_array($json)) {
            enviar_resposta_json(resposta_json(false, 'JSON inválido.', new stdClass()), 400);
        }

        $origem = preg_replace('/[^a-z_]/i', '', (string) ($json['origem'] ?? 'balcao'));
        /* Painel: apenas mesa ou balcão (sem delivery). */
        if (!in_array($origem, ['mesa', 'balcao'], true)) {
            $origem = 'balcao';
        }

        $mesa = isset($json['identificador_mesa']) ? substr((string) $json['identificador_mesa'], 0, 60) : null;
        $taxa = isset($json['taxa_entrega']) ? (float) $json['taxa_entrega'] : 0;
        $desc = isset($json['desconto']) ? (float) $json['desconto'] : 0;
        $obs = isset($json['observacoes']) ? substr((string) $json['observacoes'], 0, 500) : null;

        $id_cliente = null;
        if (!empty($json['id_cliente'])) {
            $id_cliente = (int) $json['id_cliente'];
        } elseif (!empty($json['cliente_rapido']['nome'])) {
            $cr = $json['cliente_rapido'];
            $ins = $conexao_pdo->prepare(
                'INSERT INTO clientes (nome, telefone, rua, numero, bairro) VALUES (?,?,?,?,?)'
            );
            $ins->execute([
                substr((string) ($cr['nome'] ?? 'Cliente'), 0, 120),
                isset($cr['telefone']) ? substr((string) $cr['telefone'], 0, 45) : null,
                isset($cr['rua']) ? substr((string) $cr['rua'], 0, 120) : null,
                isset($cr['numero']) ? substr((string) $cr['numero'], 0, 45) : null,
                isset($cr['bairro']) ? substr((string) $cr['bairro'], 0, 120) : null,
            ]);
            $id_cliente = (int) $conexao_pdo->lastInsertId();
        }

        $uid = (int) ($_SESSION['id_usuario_logado'] ?? 0) ?: null;

        $conexao_pdo->beginTransaction();
        try {
            $ins = $conexao_pdo->prepare(
                'INSERT INTO pedidos (id_cliente, valor_total, origem, identificador_mesa, status, id_usuario, taxa_entrega, desconto, observacoes)
                 VALUES (?, 0, ?, ?, \'pendente\', ?, ?, ?, ?)'
            );
            $ins->execute([$id_cliente, $origem, $mesa, $uid, $taxa, $desc, $obs]);
            $pid = (int) $conexao_pdo->lastInsertId();
            recalcular_total_pedido($conexao_pdo, $pid);
            $conexao_pdo->commit();
            enviar_resposta_json(resposta_json(true, 'Pedido criado.', ['id_pedido' => $pid]));
        } catch (Throwable $e) {
            $conexao_pdo->rollBack();
            throw $e;
        }
    }

    if ($metodo === 'POST' && $acao === 'adicionar_item') {
        if (!is_array($json)) {
            enviar_resposta_json(resposta_json(false, 'JSON inválido.', new stdClass()), 400);
        }

        $id_pedido = (int) ($json['id_pedido'] ?? $json['pedido_id'] ?? 0);
        $id_item = (int) ($json['id_item'] ?? $json['item_id'] ?? 0);
        $qtd = max(1, (int) ($json['quantidade'] ?? 1));

        if ($id_pedido < 1 || $id_item < 1) {
            enviar_resposta_json(resposta_json(false, 'id_pedido e id_item são obrigatórios.', new stdClass()), 400);
        }

        $st = $conexao_pdo->prepare('SELECT status FROM pedidos WHERE id_pedido = ?');
        $st->execute([$id_pedido]);
        $stt = $st->fetchColumn();
        if (!$stt || in_array($stt, ['concluido', 'cancelado'], true)) {
            enviar_resposta_json(resposta_json(false, 'Pedido não permite alteração.', new stdClass()), 400);
        }

        $st = $conexao_pdo->prepare('SELECT preco FROM itens_do_cardapio WHERE id_item = ? AND ativo = 1');
        $st->execute([$id_item]);
        $preco = (float) $st->fetchColumn();
        if ($preco <= 0) {
            enviar_resposta_json(resposta_json(false, 'Item do cardápio inválido ou inativo.', new stdClass()), 400);
        }

        $subtotal = round($qtd * $preco, 2);
        $conexao_pdo->beginTransaction();
        try {
            $ins = $conexao_pdo->prepare(
                'INSERT INTO itens_do_pedido (id_pedido, id_item, quantidade, preco_unitario, subtotal) VALUES (?,?,?,?,?)'
            );
            $ins->execute([$id_pedido, $id_item, $qtd, $preco, $subtotal]);
            $id_linha = (int) $conexao_pdo->lastInsertId();

            if (!empty($json['adicionais']) && is_array($json['adicionais'])) {
                foreach ($json['adicionais'] as $ad) {
                    $aid = (int) ($ad['id_adicional'] ?? 0);
                    $qa = max(1, (int) ($ad['quantidade'] ?? 1));
                    if ($aid < 1) {
                        continue;
                    }
                    $sa = $conexao_pdo->prepare('SELECT valor_adicional, ativo FROM adicionais WHERE id_adicional = ?');
                    $sa->execute([$aid]);
                    $rowa = $sa->fetch(PDO::FETCH_ASSOC);
                    if (!$rowa || !(int) $rowa['ativo']) {
                        continue;
                    }
                    $vu = (float) $rowa['valor_adicional'];
                    $subA = round($qa * $vu, 2);
                    $ina = $conexao_pdo->prepare(
                        'INSERT INTO adicionais_do_item_do_pedido (id_item_pedido, id_adicional, quantidade, valor_unitario, subtotal)
                         VALUES (?,?,?,?,?)'
                    );
                    $ina->execute([$id_linha, $aid, $qa, $vu, $subA]);
                }
            }

            recalcular_total_pedido($conexao_pdo, $id_pedido);
            $conexao_pdo->commit();
            enviar_resposta_json(resposta_json(true, 'Item adicionado ao pedido.', ['id_item_pedido' => $id_linha]));
        } catch (Throwable $e) {
            $conexao_pdo->rollBack();
            throw $e;
        }
    }

    if ($metodo === 'POST' && $acao === 'atualizar_status') {
        if (!is_array($json)) {
            enviar_resposta_json(resposta_json(false, 'JSON inválido.', new stdClass()), 400);
        }
        $id_pedido = (int) ($json['id_pedido'] ?? $json['pedido_id'] ?? 0);
        $status = (string) ($json['status'] ?? '');
        $permitidos = ['pendente', 'em_preparo', 'em_entrega', 'concluido', 'cancelado'];
        if ($id_pedido < 1 || !in_array($status, $permitidos, true)) {
            enviar_resposta_json(resposta_json(false, 'Status ou pedido inválido.', new stdClass()), 400);
        }

        $up = $conexao_pdo->prepare('UPDATE pedidos SET status = ? WHERE id_pedido = ?');
        $up->execute([$status, $id_pedido]);

        enviar_resposta_json(resposta_json(true, 'Status atualizado.', ['id_pedido' => $id_pedido, 'status' => $status]));
    }

    if ($metodo === 'POST' && $acao === 'concluir') {
        if (!is_array($json)) {
            enviar_resposta_json(resposta_json(false, 'JSON inválido.', new stdClass()), 400);
        }
        $id_pedido = (int) ($json['id_pedido'] ?? $json['pedido_id'] ?? 0);
        if ($id_pedido < 1) {
            enviar_resposta_json(resposta_json(false, 'id_pedido obrigatório.', new stdClass()), 400);
        }

        $conexao_pdo->beginTransaction();
        try {
            recalcular_total_pedido($conexao_pdo, $id_pedido);
            $st = $conexao_pdo->prepare('SELECT valor_total, status FROM pedidos WHERE id_pedido = ?');
            $st->execute([$id_pedido]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row || $row['status'] === 'cancelado') {
                $conexao_pdo->rollBack();
                enviar_resposta_json(resposta_json(false, 'Pedido inválido ou cancelado.', new stdClass()), 400);
            }

            if ($row['status'] === 'concluido') {
                $conexao_pdo->rollBack();
                enviar_resposta_json(resposta_json(false, 'Pedido já está concluído.', new stdClass()), 400);
            }

            $valor = (float) $row['valor_total'];
            $up = $conexao_pdo->prepare("UPDATE pedidos SET status = 'concluido' WHERE id_pedido = ?");
            $up->execute([$id_pedido]);

            $uid = (int) ($_SESSION['id_usuario_logado'] ?? 0) ?: null;
            $sid = obter_id_sessao_caixa_aberta($conexao_pdo);
            if (movimentacoes_do_caixa_possui_coluna_sessao($conexao_pdo)) {
                $mov = $conexao_pdo->prepare(
                    'INSERT INTO movimentacoes_do_caixa (tipo, origem, valor, descricao, id_pedido, id_usuario, id_sessao_do_caixa)
                     VALUES (\'entrada\', \'pedido\', ?, ?, ?, ?, ?)'
                );
                $mov->execute([
                    $valor,
                    'Entrada referente ao pedido #' . $id_pedido,
                    $id_pedido,
                    $uid,
                    $sid,
                ]);
            } else {
                $mov = $conexao_pdo->prepare(
                    'INSERT INTO movimentacoes_do_caixa (tipo, origem, valor, descricao, id_pedido, id_usuario)
                     VALUES (\'entrada\', \'pedido\', ?, ?, ?, ?)'
                );
                $mov->execute([
                    $valor,
                    'Entrada referente ao pedido #' . $id_pedido,
                    $id_pedido,
                    $uid,
                ]);
            }

            $conexao_pdo->commit();
            enviar_resposta_json(resposta_json(true, 'Pedido concluído e registrado no caixa.', ['id_pedido' => $id_pedido]));
        } catch (Throwable $e) {
            $conexao_pdo->rollBack();
            throw $e;
        }
    }

    if ($metodo === 'POST' && $acao === 'cancelar') {
        if (!is_array($json)) {
            enviar_resposta_json(resposta_json(false, 'JSON inválido.', new stdClass()), 400);
        }
        $id_pedido = (int) ($json['id_pedido'] ?? $json['pedido_id'] ?? 0);
        if ($id_pedido < 1) {
            enviar_resposta_json(resposta_json(false, 'id_pedido obrigatório.', new stdClass()), 400);
        }

        $up = $conexao_pdo->prepare("UPDATE pedidos SET status = 'cancelado' WHERE id_pedido = ?");
        $up->execute([$id_pedido]);

        enviar_resposta_json(resposta_json(true, 'Pedido cancelado.', ['id_pedido' => $id_pedido]));
    }

    enviar_resposta_json(resposta_json(false, 'Ação não reconhecida ou método inválido.', ['acao' => $acao, 'metodo' => $metodo]), 400);
} catch (Throwable $e) {
    enviar_resposta_json(resposta_json(false, 'Erro no servidor: ' . $e->getMessage(), new stdClass()), 500);
}
