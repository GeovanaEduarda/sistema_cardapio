<?php
require_once dirname(__DIR__) . '/configuracao/caminhos_da_aplicacao.php';
?>
<nav>

    <h1 id="logo_nav">Sistema de <br><span>Gerenciamento</span></h1>

    <ul id="nav_link">

        <li class="pages_link">
            <a href="<?php echo htmlspecialchars(url_da_aplicacao('publico/painel/pedidos.php')); ?>">
                <i class="fa-solid fa-truck"></i><span>Pedidos</span>
            </a>
        </li>

        <li class="pages_link">
            <a href="<?php echo htmlspecialchars(url_da_aplicacao('publico/painel/cardapio.php')); ?>">
                <i class="fa-solid fa-book-open"></i><span>Cardápio</span>
            </a>
        </li>

        <li class="pages_link">
            <a href="<?php echo htmlspecialchars(url_da_aplicacao('publico/painel/itens_excluidos.php')); ?>">
                <i class="fa-solid fa-trash"></i><span>Lixeira</span>
            </a>
        </li>

        <li class="pages_link">
            <a href="<?php echo htmlspecialchars(url_da_aplicacao('publico/painel/financeiro.php')); ?>">
                <i class="fa-solid fa-dollar-sign"></i><span>Financeiro</span>
            </a>
        </li>

        <li class="pages_link">
            <a href="<?php echo htmlspecialchars(url_da_aplicacao('publico/painel/funcionarios.php')); ?>">
                <i class="fa-solid fa-users"></i><span>Funcionários</span>
            </a>
        </li>

        <li class="pages_link">
            <a href="<?php echo htmlspecialchars(url_da_aplicacao('publico/perfil/perfil.php')); ?>">
                <i class="fa-solid fa-user"></i><span>Perfil</span>
            </a>
        </li>

        <li class="pages_link">
            <a href="<?php echo htmlspecialchars(url_da_aplicacao('apis/encerrar_sessao.php')); ?>">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Sair</span>
            </a>
        </li>

    </ul>

</nav>
