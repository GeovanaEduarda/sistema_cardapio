<?php
require_once dirname(__DIR__, 2) . '/configuracao/caminhos_da_aplicacao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_da_aplicacao('recursos/css/estilos.css')); ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js" crossorigin="anonymous"></script>
    <title>Sistema de Gerenciamento</title>
</head>

<body id="body_index" >

    <main class="main_index">

        <div id="header_index"></div>

        <form action="">
            <div class="input_group">
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" placeholder="Digite o nome do funcionário">
            </div>

            <div class="input_group">
                <label for="email">E-mail Corporativo</label>
                <input type="email" id="email" placeholder="funcionario@empresa.com">
            </div>

            <div class="input_group">
                <label for="cargo">Cargo</label>
                <select id="cargo" name="id_cargo">
                    <option value="1">Gerente</option>
                    <option value="2">Cozinheiro</option>
                    <option value="3">Garçom</option>
                </select>
            </div>

            <div class="input_group">
                <label for="tel">Telefone</label>
                <input type="tel" id="tel" placeholder="(00) 00000-0000">
            </div>

            <a href="<?php echo htmlspecialchars(url_da_aplicacao('publico/painel/pedidos.php')); ?>" class="btn_login">Ir ao painel (após cadastro pelo admin)</a>

        </form>

        <section class="conta">
            <p class="cadastro">
                Já tem uma conta? <a href="<?php echo htmlspecialchars(url_da_aplicacao('index.php')); ?>">Entrar</a>
            </p>
        </section>

        <footer class="footer_index">
            <p>© 2026 Sistema de Gerenciamento - Todos os direitos reservados</p>
            <p><i class="fa-solid fa-circle-info"></i> Precisa de ajuda? <a href="<?php echo htmlspecialchars(url_da_aplicacao('publico/painel/suporte.php')); ?>">Suporte Técnico</a></p>
        </footer>

    </main>

     <script src="<?php echo htmlspecialchars(url_da_aplicacao('recursos/js/scripts_painel.js')); ?>"></script>


</body>
</html>
