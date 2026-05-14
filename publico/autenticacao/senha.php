<?php
require_once dirname(__DIR__, 2) . '/configuracao/caminhos_da_aplicacao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_da_aplicacao('recursos/css/estilos.css')); ?>">
    <title>Recuperar senha</title>
</head>
<body id="body_index">
    <main class="main_index">
        <h2 style="text-align:center;margin-bottom:12px;color:var(--cor-titles);">Esqueceu a senha?</h2>
        <p style="text-align:center;color:var(--cinza-texto);font-size:0.95rem;">
            Entre em contato com um administrador do sistema para redefinir sua senha.
        </p>
        <p style="text-align:center;margin-top:24px;">
            <a class="btn_login" href="<?php echo htmlspecialchars(url_da_aplicacao('index.php')); ?>">Voltar ao login</a>
        </p>
    </main>
</body>
</html>
