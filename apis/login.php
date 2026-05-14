<?php
/**
 * API de Autenticação - Sistema de Cardápio
 * Suporta POST urlencoded e JSON
 * Retorna sessão ou resposta JSON conforme requisição
 */

declare(strict_types=1);
session_start();

require_once dirname(__DIR__) . '/configuracao/conexao_banco.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Capturar dados de login
    $login = '';
    $senha = '';

    // Se vier como JSON
    if ($_SERVER['CONTENT_TYPE'] === 'application/json' || 
        (isset($_SERVER['HTTP_CONTENT_TYPE']) && $_SERVER['HTTP_CONTENT_TYPE'] === 'application/json')) {
        
        $json = json_decode(file_get_contents('php://input'), true);
        $login = trim($json['login'] ?? '');
        $senha = $json['senha'] ?? '';
    } else {
        // Se vier como form data
        $login = trim($_POST['login'] ?? '');
        $senha = $_POST['senha'] ?? '';
    }

    // Validação
    if (empty($login) || empty($senha)) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Usuário e senha são obrigatórios.'
        ]);
        exit;
    }

    // Buscar usuário no banco
    $sql = "SELECT 
                id, 
                nome, 
                email, 
                usuario, 
                senha, 
                ativo, 
                perfil
            FROM usuarios 
            WHERE (usuario = :login OR email = :login) 
            AND ativo = 1
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':login' => $login
    ]);
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar se usuário existe
    if (!$usuario) {
        http_response_code(401);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Usuário ou senha inválidos.'
        ]);
        exit;
    }

    // Verificar senha
    if (!password_verify($senha, $usuario['senha'])) {
        http_response_code(401);
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Usuário ou senha inválidos.'
        ]);
        exit;
    }

    // Login bem-sucedido
    // Criar sessão
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_login'] = $usuario['usuario'];
    $_SESSION['usuario_perfil'] = $usuario['perfil'];
    $_SESSION['login_time'] = time();

    // Log de acesso (opcional)
    try {
        $log_sql = "INSERT INTO logs_acesso (usuario_id, tipo, descricao, ip_address, data_hora) 
                    VALUES (:usuario_id, :tipo, :descricao, :ip_address, NOW())";
        $log_stmt = $pdo->prepare($log_sql);
        $log_stmt->execute([
            ':usuario_id' => $usuario['id'],
            ':tipo' => 'login',
            ':descricao' => 'Login realizado com sucesso',
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'desconhecido'
        ]);
    } catch (Exception $e) {
        // Ignorar erro de log, não afeta o login
    }

    // Retornar sucesso
    http_response_code(200);
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Login realizado com sucesso.',
        'usuario' => [
            'id' => (int)$usuario['id'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'perfil' => $usuario['perfil']
        ]
    ]);
    exit;

} catch (PDOException $e) {
    error_log('Erro de autenticação: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao conectar ao banco de dados.'
    ]);
    exit;
} catch (Exception $e) {
    error_log('Erro geral de autenticação: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao processar login.'
    ]);
    exit;
}
?>
