<?php
/**
 * Logout - Destruir sessão
 */
session_start();

// Log de saída (opcional)
if (isset($_SESSION['usuario_id'])) {
    try {
        require_once dirname(__DIR__) . '/configuracao/conexao_banco.php';
        
        $log_sql = "INSERT INTO logs_acesso (usuario_id, tipo, descricao, ip_address, data_hora) 
                    VALUES (:usuario_id, :tipo, :descricao, :ip_address, NOW())";
        $log_stmt = $pdo->prepare($log_sql);
        $log_stmt->execute([
            ':usuario_id' => $_SESSION['usuario_id'],
            ':tipo' => 'logout',
            ':descricao' => 'Logout realizado',
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'desconhecido'
        ]);
    } catch (Exception $e) {
        // Ignorar erro de log
    }
}

// Destruir sessão
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

// Redirecionar para login
header('Location: index.php');
exit;
?>
