<?php
require_once dirname(__DIR__) . '/configuracao/sessao_api.php';
require_once dirname(__DIR__) . '/configuracao/conexao_banco.php';
require_once dirname(__DIR__) . '/configuracao/resposta_json.php';

error_reporting(0);
ini_set('display_errors', 0);

$METHOD = $_SERVER['REQUEST_METHOD'];
$acao = $_GET['acao'] ?? null;
$status_filtro = $_GET['status'] ?? 'ativos';
$input = file_get_contents('php://input');
$data = is_array(json_decode($input, true)) ? json_decode($input, true) : [];

try {
    switch ($METHOD) {
        case 'GET':
            if ($acao == 'listar_categorias') {
                $valor_ativo = ($status_filtro == 'excluidos') ? 0 : 1;
                $sql = 'SELECT id_categoria, nome_categoria FROM categorias WHERE ativo = ? ORDER BY id_categoria DESC';
                $stmt = $conexao_pdo->prepare($sql);
                $stmt->execute([$valor_ativo]);
                $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                enviar_resposta_json(resposta_json(true, 'Categorias carregadas.', ['categorias' => $linhas]));
            }
            enviar_resposta_json(resposta_json(false, 'Ação GET inválida.', ['acao' => $acao]), 400);
            break;

        case 'POST':
            if ($acao == 'restaurar_categoria') {
                $id = $_GET['id'] ?? null;
                if ($id) {
                    $stmt = $conexao_pdo->prepare('UPDATE categorias SET ativo = 1 WHERE id_categoria = ?');
                    $sucesso = $stmt->execute([$id]);
                    enviar_resposta_json(resposta_json((bool) $sucesso, $sucesso ? 'Categoria restaurada.' : 'Falha ao restaurar.', ['id' => (int) $id]));
                }
                enviar_resposta_json(resposta_json(false, 'ID não informado.', new stdClass()), 400);
            }
            if ($acao === 'excluir_definitivo') {
                $id = (int) ($_GET['id'] ?? 0);
                if ($id < 1) {
                    enviar_resposta_json(resposta_json(false, 'ID inválido.', new stdClass()), 400);
                }
                try {
                    $stmt = $conexao_pdo->prepare('DELETE FROM categorias WHERE id_categoria = ? AND ativo = 0');
                    $stmt->execute([$id]);
                    if ($stmt->rowCount() < 1) {
                        enviar_resposta_json(resposta_json(false, 'Categoria não encontrada na lixeira ou ainda ativa.', new stdClass()), 400);
                    }
                    enviar_resposta_json(resposta_json(true, 'Categoria removida permanentemente.', ['id' => $id]));
                } catch (Throwable $e) {
                    enviar_resposta_json(resposta_json(false, 'Não foi possível excluir: existem produtos ou adicionais vinculados. Remova-os da lixeira primeiro.', new stdClass()), 400);
                }
            }
            if ($acao == 'cadastrar_categoria') {
                if (empty($data['nome_categoria'])) {
                    enviar_resposta_json(resposta_json(false, 'Nome obrigatório.', new stdClass()), 400);
                }
                $stmt = $conexao_pdo->prepare('INSERT INTO categorias (nome_categoria, ativo) VALUES (?, 1)');
                $sucesso = $stmt->execute([$data['nome_categoria']]);
                enviar_resposta_json(resposta_json((bool) $sucesso, $sucesso ? 'Categoria criada.' : 'Falha ao salvar.', ['id' => (int) $conexao_pdo->lastInsertId()]));
            }
            if ($acao == 'editar_categoria') {
                if (empty($data['id_categoria']) || empty($data['nome_categoria'])) {
                    enviar_resposta_json(resposta_json(false, 'Dados incompletos.', new stdClass()), 400);
                }
                $stmt = $conexao_pdo->prepare('UPDATE categorias SET nome_categoria = ? WHERE id_categoria = ?');
                $sucesso = $stmt->execute([$data['nome_categoria'], $data['id_categoria']]);
                enviar_resposta_json(resposta_json((bool) $sucesso, $sucesso ? 'Categoria atualizada.' : 'Falha ao atualizar.', new stdClass()));
            }
            enviar_resposta_json(resposta_json(false, 'Ação POST inválida.', ['acao' => $acao]), 400);
            break;

        case 'PUT':
            if ($acao == 'editar_categoria') {
                if (empty($data['id_categoria']) || empty($data['nome_categoria'])) {
                    enviar_resposta_json(resposta_json(false, 'Dados incompletos.', new stdClass()), 400);
                }
                $stmt = $conexao_pdo->prepare('UPDATE categorias SET nome_categoria = ? WHERE id_categoria = ?');
                $sucesso = $stmt->execute([$data['nome_categoria'], $data['id_categoria']]);
                enviar_resposta_json(resposta_json((bool) $sucesso, $sucesso ? 'Categoria atualizada.' : 'Falha ao atualizar.', new stdClass()));
            }
            enviar_resposta_json(resposta_json(false, 'Ação PUT inválida.', ['acao' => $acao]), 400);
            break;

        case 'DELETE':
            if ($acao == 'deletar_categoria') {
                $id = $_GET['id'] ?? null;
                if ($id) {
                    $stmt = $conexao_pdo->prepare('UPDATE categorias SET ativo = 0 WHERE id_categoria = ?');
                    $sucesso = $stmt->execute([$id]);
                    enviar_resposta_json(resposta_json((bool) $sucesso, $sucesso ? 'Categoria movida para a lixeira.' : 'Falha.', ['id' => (int) $id]));
                }
                enviar_resposta_json(resposta_json(false, 'ID não informado.', new stdClass()), 400);
            }
            enviar_resposta_json(resposta_json(false, 'Ação DELETE inválida.', ['acao' => $acao]), 400);
            break;

        default:
            enviar_resposta_json(resposta_json(false, 'Método não suportado.', new stdClass()), 405);
    }
} catch (Throwable $e) {
    enviar_resposta_json(resposta_json(false, 'Erro interno: ' . $e->getMessage(), new stdClass()), 500);
}
