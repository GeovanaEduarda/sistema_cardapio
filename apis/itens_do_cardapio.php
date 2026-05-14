<?php
require_once dirname(__DIR__) . '/configuracao/sessao_api.php';
require_once dirname(__DIR__) . '/configuracao/conexao_banco.php';
require_once dirname(__DIR__) . '/configuracao/auxiliar_upload_imagem.php';
require_once dirname(__DIR__) . '/configuracao/resposta_json.php';

$METHOD = $_SERVER['REQUEST_METHOD'];
$acao = $_GET['acao'] ?? null;
$status_filtro = $_GET['status'] ?? 'ativos';

try {
    switch ($METHOD) {
        case 'GET':
            if ($acao == 'listar_itens') {
                $valor_ativo = ($status_filtro == 'excluidos') ? 0 : 1;

                $sql = 'SELECT 
                            item.id_item, 
                            item.nome as nome, 
                            item.descricao, 
                            item.preco as preco, 
                            item.url_imagem, 
                            item.id_categoria,
                            cat.nome_categoria 
                        FROM itens_do_cardapio item 
                        INNER JOIN categorias cat ON item.id_categoria = cat.id_categoria 
                        WHERE item.ativo = ? 
                        ORDER BY item.id_item DESC';

                $stmt = $conexao_pdo->prepare($sql);
                $stmt->execute([$valor_ativo]);
                $linhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                enviar_resposta_json(resposta_json(true, 'Itens carregados.', ['itens' => $linhas]));
            }
            enviar_resposta_json(resposta_json(false, 'Ação GET inválida.', ['acao' => $acao]), 400);
            break;

        case 'POST':
            if ($acao == 'restaurar_item') {
                $id = $_GET['id'] ?? null;
                if ($id) {
                    $stmt = $conexao_pdo->prepare('UPDATE itens_do_cardapio SET ativo = 1 WHERE id_item = ?');
                    $sucesso = $stmt->execute([$id]);
                    enviar_resposta_json(resposta_json((bool) $sucesso, $sucesso ? 'Item restaurado.' : 'Falha.', ['id' => (int) $id]));
                }
                enviar_resposta_json(resposta_json(false, 'ID não fornecido.', new stdClass()), 400);
            }
            if ($acao === 'excluir_definitivo') {
                $id = (int) ($_GET['id'] ?? 0);
                if ($id < 1) {
                    enviar_resposta_json(resposta_json(false, 'ID inválido.', new stdClass()), 400);
                }
                try {
                    $stmt = $conexao_pdo->prepare('DELETE FROM itens_do_cardapio WHERE id_item = ? AND ativo = 0');
                    $stmt->execute([$id]);
                    if ($stmt->rowCount() < 1) {
                        enviar_resposta_json(resposta_json(false, 'Item não está na lixeira.', new stdClass()), 400);
                    }
                    enviar_resposta_json(resposta_json(true, 'Produto removido permanentemente.', ['id' => $id]));
                } catch (Throwable $e) {
                    enviar_resposta_json(resposta_json(false, 'Não foi possível excluir: item ainda referenciado em pedidos.', new stdClass()), 400);
                }
            }

            if ($acao == 'cadastrar_item' || $acao == 'editar_item') {
                $id = $_POST['id_item'] ?? $_POST['id_item_cardapio'] ?? null;
                $nome = $_POST['nome_item'] ?? '';
                $preco = $_POST['valor_item'] ?? 0;
                $cat_id = $_POST['id_categoria'] ?? null;
                $desc = $_POST['descricao_item'] ?? '';
                $url_imagem = $_POST['imagem_atual'] ?? '';

                if (isset($_FILES['foto_item']) && ($_FILES['foto_item']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                    $resultado_upload = salvar_upload_imagem_seguro($_FILES['foto_item']);
                    if (!$resultado_upload['sucesso']) {
                        enviar_resposta_json(resposta_json(false, $resultado_upload['mensagem'] ?? 'Falha no upload', new stdClass()), 400);
                    }
                    $url_imagem = $resultado_upload['caminho_relativo'];
                }

                if ($acao == 'cadastrar_item') {
                    $sql = 'INSERT INTO itens_do_cardapio (nome, descricao, preco, url_imagem, id_categoria, ativo) VALUES (?, ?, ?, ?, ?, 1)';
                    $stmt = $conexao_pdo->prepare($sql);
                    $sucesso = $stmt->execute([$nome, $desc, $preco, $url_imagem, $cat_id]);
                    $novo_id = (int) $conexao_pdo->lastInsertId();
                } else {
                    $sql = 'UPDATE itens_do_cardapio SET nome = ?, descricao = ?, preco = ?, url_imagem = ?, id_categoria = ? WHERE id_item = ?';
                    $stmt = $conexao_pdo->prepare($sql);
                    $sucesso = $stmt->execute([$nome, $desc, $preco, $url_imagem, $cat_id, $id]);
                    $novo_id = (int) $id;
                }

                enviar_resposta_json(resposta_json((bool) $sucesso, $sucesso ? 'Item salvo.' : 'Erro ao salvar.', ['id' => $novo_id]));
            }
            enviar_resposta_json(resposta_json(false, 'Ação POST inválida.', ['acao' => $acao]), 400);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (($acao ?? '') !== 'deletar_item') {
                enviar_resposta_json(resposta_json(false, 'Informe acao=deletar_item.', ['acao' => $acao]), 400);
            }
            if ($id) {
                $stmt = $conexao_pdo->prepare('UPDATE itens_do_cardapio SET ativo = 0 WHERE id_item = ?');
                enviar_resposta_json(resposta_json((bool) $stmt->execute([$id]), 'Item movido para a lixeira.', ['id' => (int) $id]));
            }
            enviar_resposta_json(resposta_json(false, 'ID não informado.', new stdClass()), 400);
            break;

        default:
            enviar_resposta_json(resposta_json(false, 'Método não suportado.', new stdClass()), 405);
    }
} catch (Throwable $e) {
    enviar_resposta_json(resposta_json(false, 'Erro interno.', new stdClass()), 500);
}
