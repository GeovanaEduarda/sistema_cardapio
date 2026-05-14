<?php
require_once dirname(__DIR__) . '/configuracao/sessao_api.php';
require_once dirname(__DIR__) . '/configuracao/conexao_banco.php';
require_once dirname(__DIR__) . '/configuracao/caminhos_da_aplicacao.php';
require_once dirname(__DIR__) . '/configuracao/auxiliar_upload_imagem.php';
require_once dirname(__DIR__) . '/configuracao/resposta_json.php';

error_reporting(0);
ini_set('display_errors', 0);

try {
    $METHOD = $_SERVER['REQUEST_METHOD'];
    $acao = $_GET['acao'] ?? null;

    switch ($METHOD) {
        case 'GET':
            if ($acao == 'listar_usuarios') {
                $sql = 'SELECT id_usuario, nome, login, nivel, ativo, url_imagem FROM usuarios_administradores ORDER BY nome ASC';
                $stmt = $conexao_pdo->query($sql);
                $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($usuarios as &$u) {
                    $u['url_imagem'] = url_publica_imagem($u['url_imagem'] ?? '');
                }
                unset($u);

                enviar_resposta_json(resposta_json(true, 'Usuários carregados.', ['usuarios' => $usuarios]));
            }
            enviar_resposta_json(resposta_json(false, 'Ação GET inválida.', ['acao' => $acao]), 400);
            break;

        case 'PUT':
            if ($acao == 'alterar_status') {
                $data = json_decode(file_get_contents('php://input'), true);
                if (!is_array($data) || !isset($data['id_usuario'])) {
                    enviar_resposta_json(resposta_json(false, 'ID não informado.', new stdClass()), 400);
                }

                $sql = 'UPDATE usuarios_administradores SET ativo = CASE WHEN ativo = 1 THEN 0 ELSE 1 END WHERE id_usuario = ?';
                $stmt = $conexao_pdo->prepare($sql);
                $res = $stmt->execute([$data['id_usuario']]);

                enviar_resposta_json(resposta_json((bool) $res, $res ? 'Status atualizado.' : 'Erro ao atualizar.', new stdClass()));
            }
            enviar_resposta_json(resposta_json(false, 'Ação PUT inválida.', ['acao' => $acao]), 400);
            break;

        case 'POST':
            if ($acao == 'cadastrar_usuario') {
                $nome = isset($_POST['nome']) ? trim((string) $_POST['nome']) : null;
                $login = isset($_POST['login']) ? trim((string) $_POST['login']) : null;
                $senha = $_POST['senha'] ?? null;
                $nivel = $_POST['nivel'] ?? 'comum';

                if (!$nome || !$login || !$senha) {
                    enviar_resposta_json(resposta_json(false, 'Preencha todos os campos obrigatórios.', new stdClass()), 400);
                }

                $url_imagem = 'recursos/uploads/sem_foto.svg';

                if (isset($_FILES['foto']) && ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                    $resultado = salvar_upload_imagem_seguro($_FILES['foto']);
                    if (!$resultado['sucesso']) {
                        enviar_resposta_json(resposta_json(false, $resultado['mensagem'] ?? 'Falha no upload da foto.', new stdClass()), 400);
                    }
                    $url_imagem = $resultado['caminho_relativo'];
                }

                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

                $sql = 'INSERT INTO usuarios_administradores (nome, login, senha, nivel, ativo, url_imagem) VALUES (?, ?, ?, ?, 1, ?)';
                $stmt = $conexao_pdo->prepare($sql);
                $res = $stmt->execute([$nome, $login, $senha_hash, $nivel, $url_imagem]);

                enviar_resposta_json(resposta_json((bool) $res, $res ? 'Cadastrado com sucesso.' : 'Erro ao inserir.', ['id' => (int) $conexao_pdo->lastInsertId()]));
            }
            enviar_resposta_json(resposta_json(false, 'Ação POST inválida.', ['acao' => $acao]), 400);
            break;

        default:
            enviar_resposta_json(resposta_json(false, 'Método não suportado.', new stdClass()), 405);
    }
} catch (Throwable $e) {
    enviar_resposta_json(resposta_json(false, 'Erro de servidor: ' . $e->getMessage(), new stdClass()), 500);
}
