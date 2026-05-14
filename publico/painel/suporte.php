<?php
require_once dirname(__DIR__, 2) . '/configuracao/verificar_sessao.php';
require_once dirname(__DIR__, 2) . '/configuracao/caminhos_da_aplicacao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_da_aplicacao('recursos/css/estilos.css')); ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js" crossorigin="anonymous"></script>
    <title>Suporte</title>
</head>
<body>
    <?php include dirname(__DIR__, 2) . '/componentes/nav.php'; ?>
    <main id="main_pedidos">
        <header id="header_pedidos">
            <h2>Suporte técnico</h2>
        </header>
        <p style="color:var(--cinza-texto);max-width:640px;">
            Em caso de dúvidas ou problemas no sistema, registre o ocorrido com o administrador da sua unidade.
        </p>
        <p style="margin-top:20px;">
            <a class="btn_login" href="<?php echo htmlspecialchars(url_da_aplicacao('publico/painel/pedidos.php')); ?>">Voltar aos pedidos</a>
        </p>
    </main>
    <script src="<?php echo htmlspecialchars(url_da_aplicacao('recursos/js/scripts_painel.js')); ?>"></script>
</body>
</html>
