<?php
require_once dirname(__DIR__) . '/configuracao/sessao_api.php';
require_once dirname(__DIR__) . '/configuracao/conexao_banco.php';
require_once dirname(__DIR__) . '/configuracao/resposta_json.php';
require_once dirname(__DIR__) . '/configuracao/auxiliar_pedidos.php';

$method = $_SERVER['REQUEST_METHOD'];
$acao = $_GET['acao'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($acao === 'listar_itens_do_pedidos_adicionais') {
                $id_pedido = (int) ($_GET['id_pedido'] ?? $_GET['pedido_id'] ?? 0);
                if ($id_pedido < 1) {
                    enviar_resposta_json(resposta_json(false, 'id_pedido obrigatório.', new stdClass()), 400);
                }
                $sql = 'SELECT ipa.id_adicional_item_pedido, i.id_item_pedido, a.id_adicional, a.nome_adicional, ipa.quantidade, ipa.valor_unitario, ipa.subtotal
                        FROM adicionais_do_item_do_pedido ipa
                        INNER JOIN itens_do_pedido i ON i.id_item_pedido = ipa.id_item_pedido
                        INNER JOIN adicionais a ON a.id_adicional = ipa.id_adicional
                        WHERE i.id_pedido = ?';
                $stmt = $conexao_pdo->prepare($sql);
                $stmt->execute([$id_pedido]);
                enviar_resposta_json(resposta_json(true, 'OK', ['linhas' => $stmt->fetchAll(PDO::FETCH_ASSOC)]));
            }
            enviar_resposta_json(resposta_json(false, 'Ação GET inválida.', ['acao' => $acao]), 400);
            break;

        case 'POST':
            if ($acao === 'adicionar_item_pedido_adicional') {
                $data = json_decode(file_get_contents('php://input'), true);
                if (!is_array($data)) {
                    enviar_resposta_json(resposta_json(false, 'JSON inválido.', new stdClass()), 400);
                }
                $id_item_pedido = (int) ($data['id_item_pedido'] ?? 0);
                $id_adicional = (int) ($data['id_adicional'] ?? 0);
                $qtd = max(1, (int) ($data['quantidade'] ?? 1));
                if ($id_item_pedido < 1 || $id_adicional < 1) {
                    enviar_resposta_json(resposta_json(false, 'id_item_pedido e id_adicional obrigatórios.', new stdClass()), 400);
                }

                $sa = $conexao_pdo->prepare('SELECT valor_adicional, ativo FROM adicionais WHERE id_adicional = ?');
                $sa->execute([$id_adicional]);
                $rowa = $sa->fetch(PDO::FETCH_ASSOC);
                if (!$rowa || !(int) $rowa['ativo']) {
                    enviar_resposta_json(resposta_json(false, 'Adicional inválido.', new stdClass()), 400);
                }
                $vu = (float) $rowa['valor_adicional'];
                $sub = round($qtd * $vu, 2);

                $sql = 'INSERT INTO adicionais_do_item_do_pedido (id_item_pedido, id_adicional, quantidade, valor_unitario, subtotal) VALUES (?, ?, ?, ?, ?)';
                $stmt = $conexao_pdo->prepare($sql);
                $sucesso = $stmt->execute([$id_item_pedido, $id_adicional, $qtd, $vu, $sub]);

                $stPed = $conexao_pdo->prepare('SELECT id_pedido FROM itens_do_pedido WHERE id_item_pedido = ?');
                $stPed->execute([$id_item_pedido]);
                $pid = (int) $stPed->fetchColumn();
                if ($pid > 0) {
                    recalcular_total_pedido($conexao_pdo, $pid);
                }

                enviar_resposta_json(resposta_json((bool) $sucesso, $sucesso ? 'Adicional vinculado.' : 'Falha.', ['id' => (int) $conexao_pdo->lastInsertId()]));
            }
            enviar_resposta_json(resposta_json(false, 'Ação POST inválida.', ['acao' => $acao]), 400);
            break;

        default:
            enviar_resposta_json(resposta_json(false, 'Método não suportado.', new stdClass()), 405);
    }
} catch (Throwable $e) {
    enviar_resposta_json(resposta_json(false, 'Erro no servidor ao processar adicionais do pedido.', new stdClass()), 500);
}
