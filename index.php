<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['id_usuario_logado'])) {
    header('Location: ./publico/painel/pedidos.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Login</title>
    <link rel="stylesheet" href="./recursos/css/estilos.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
</head>
<body id="body_index">
    <div class="login-container">
        <form id="form_login" class="login-form">
            <h2>Bem-vindo</h2>
            <p>Por favor, faça login na sua conta.</p>
            
            <div id="loginError" class="login-error" role="alert" aria-live="assertive"></div>
            
            <div class="input-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="seu@email.com" required>
            </div>

            <div class="input-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="senha" placeholder="••••••••" required>
            </div>

            <div class="options">
                <a href="./publico/autenticacao/senha.php">Esqueceu a senha?</a>
            </div>

            <button type="submit" class="login-btn">Entrar</button>

            <div class="footer">
                Precisa de ajuda? <a href="./publico/painel/suporte.php">Suporte</a>
            </div>
        </form>
    </div>

    <script src="./recursos/js/login.js"></script>
</body>
</html>