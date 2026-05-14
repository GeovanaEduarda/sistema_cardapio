<?php
require_once dirname(__DIR__) . '/configuracao/sessao_api.php';
require_once dirname(__DIR__) . '/configuracao/conexao_banco.php';
require_once dirname(__DIR__) . '/configuracao/resposta_json.php';

error_reporting(0);
ini_set('display_errors', 0);

$method = $_SERVER['REQUEST_METHOD'];
$acao = $_GET['acao'] ?? '';
$status_filtro = $_GET['status'] ?? 'ativos';

try {
    switch ($method) {
        case 'GET':
            if ($acao === 'listar_adicionais') {
                $valor_ativo = ($status_filtro == 'excluidos') ? 0 : 1;

                $sql = 'SELECT a.id_adicional, a.nome_adicional, a.valor_adicional, a.id_categoria, c.nome_categoria 
                        FROM adicionais a 
                        INNER JOIN categorias c ON a.id_categoria = c.id_categoria
                        WHERE a.ativo = ?
                        ORDER BY a.id_adicional DESC';

                $stmt = $conexao_pdo->prepare($sql);
                $stmt->execute([$valor_ativo]);
                $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                enviar_resposta_json(resposta_json(true, 'Adicionais carregados.', ['adicionais' => $linhas]));
            }
            enviar_resposta_json(resposta_json(false, 'Ação GET inválida.', ['acao' => $acao]), 400);
            break;

        case 'POST':
            if ($acao === 'restaurar_adicional') {
                $id = $_GET['id'] ?? null;
                if ($id) {
                    $stmt = $conexao_pdo->prepare('UPDATE adicionais SET ativo = 1 WHERE id_adicional = ?');
                    $sucesso = $stmt->execute([$id]);
                    enviar_resposta_json(resposta_json((bool) $sucesso, $sucesso ? 'Adicional restaurado.' : 'Falha.', ['id' => (int) $id]));
                }
                enviar_resposta_json(resposta_json(false, 'ID não informado.', new stdClass()), 400);
            }
            if ($acao === 'excluir_definitivo') {
                $id = (int) ($_GET['id'] ?? 0);
                if ($id < 1) {
                    enviar_resposta_json(resposta_json(false, 'ID inválido.', new stdClass()), 400);
                }
                try {
                    $stmt = $conexao_pdo->prepare('DELETE FROM adicionais WHERE id_adicional = ? AND ativo = 0');
                    $stmt->execute([$id]);
                    if ($stmt->rowCount() < 1) {
                        enviar_resposta_json(resposta_json(false, 'Adicional não está na lixeira.', new stdClass()), 400);
                    }
                    enviar_resposta_json(resposta_json(true, 'Adicional removido permanentemente.', ['id' => $id]));
                } catch (Throwable $e) {
                    enviar_resposta_json(resposta_json(false, 'Não foi possível excluir: registro vinculado a pedidos antigos.', new stdClass()), 400);
                }
            }
            if ($acao === 'cadastrar_adicional') {
                $data = json_decode(file_get_contents('php://input'), true);
                if (!is_array($data)) {
                    enviar_resposta_json(resposta_json(false, 'JSON inválido.', new stdClass()), 400);
                }
                $sql = 'INSERT INTO adicionais (nome_adicional, valor_adicional, id_categoria, ativo) VALUES (?, ?, ?, 1)';
                $stmt = $conexao_pdo->prepare($sql);
                $sucesso = $stmt->execute([
                    $data['nome_adicional'] ?? '',
                    $data['valor_adicional'] ?? 0,
                    $data['id_categoria'] ?? null,
                ]);
                enviar_resposta_json(resposta_json((bool) $sucesso, $sucesso ? 'Adicional criado.' : 'Falha ao salvar.', ['id' => (int) $conexao_pdo->lastInsertId()]));
            }
            if ($acao === 'editar_adicional') {
                $data = json_decode(file_get_contents('php://input'), true);
                if (!is_array($data)) {
                    enviar_resposta_json(resposta_json(false, 'JSON inválido.', new stdClass()), 400);
                }
                $sql = 'UPDATE adicionais SET nome_adicional = ?, valor_adicional = ?, id_categoria = ? WHERE id_adicional = ?';
                $stmt = $conexao_pdo->prepare($sql);
                $sucesso = $stmt->execute([
                    $data['nome_adicional'] ?? '',
                    $data['valor_adicional'] ?? 0,
                    $data['id_categoria'] ?? null,
                    $data['id_adicional'] ?? 0,
                ]);
                enviar_resposta_json(resposta_json((bool) $sucesso, $sucesso ? 'Adicional atualizado.' : 'Falha.', new stdClass()));
            }
            enviar_resposta_json(resposta_json(false, 'Ação POST inválida.', ['acao' => $acao]), 400);
            break;

        case 'PUT':
            if ($acao === 'editar_adicional') {
                $data = json_decode(file_get_contents('php://input'), true);
                if (!is_array($data)) {
                    enviar_resposta_json(resposta_json(false, 'JSON inválido.', new stdClass()), 400);
                }
                $sql = 'UPDATE adicionais SET nome_adicional = ?, valor_adicional = ?, id_categoria = ? WHERE id_adicional = ?';
                $stmt = $conexao_pdo->prepare($sql);
                $sucesso = $stmt->execute([
                    $data['nome_adicional'] ?? '',
                    $data['valor_adicional'] ?? 0,
                    $data['id_categoria'] ?? null,
                    $data['id_adicional'] ?? 0,
                ]);
                enviar_resposta_json(resposta_json((bool) $sucesso, $sucesso ? 'Adicional atualizado.' : 'Falha.', new stdClass()));
            }
            enviar_resposta_json(resposta_json(false, 'Ação PUT inválida.', ['acao' => $acao]), 400);
            break;

        case 'DELETE':
            if ($acao !== 'deletar_adicional') {
                enviar_resposta_json(resposta_json(false, 'Informe acao=deletar_adicional.', ['acao' => $acao]), 400);
            }
            $id = $_GET['id'] ?? null;
            if ($id) {
                $stmt = $conexao_pdo->prepare('UPDATE adicionais SET ativo = 0 WHERE id_adicional = ?');
                $sucesso = $stmt->execute([$id]);
                enviar_resposta_json(resposta_json((bool) $sucesso, $sucesso ? 'Adicional excluído (lixeira).' : 'Falha.', ['id' => (int) $id]));
            }
            enviar_resposta_json(resposta_json(false, 'ID não informado.', new stdClass()), 400);
            break;

        default:
            enviar_resposta_json(resposta_json(false, 'Método não suportado.', new stdClass()), 405);
    }
} catch (Throwable $e) {
    enviar_resposta_json(resposta_json(false, 'Erro interno no servidor.', new stdClass()), 500);
}
