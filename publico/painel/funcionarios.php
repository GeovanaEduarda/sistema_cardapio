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
    <title>Gerenciamento de Funcionários</title>
</head>
<body>
    <?php include dirname(__DIR__, 2) . '/componentes/nav.php'; ?>

    <main id="main_funcionarios">
        <header id="header_funcionarios">
            <h2>Gerenciamento de Funcionários</h2>
        </header>
        
        <section id="card_funcionarios_container">
            <div class="card_f">
                <i class="fa-solid fa-users azul"></i>
                <div class="info">
                    <h3 id="total_geral">0</h3>
                    <p>Total</p>
                </div>
            </div>
            <div class="card_f">
                <i class="fa-solid fa-user-plus verde"></i>
                <div class="info">
                    <h3 id="total_ativos">0</h3>
                    <p>Ativos</p>
                </div>
            </div>
            <div class="card_f">
                <i class="fa-solid fa-user-xmark vermelho"></i>
                <div class="info">
                    <h3 id="total_inativos">0</h3>
                    <p>Não ativos</p>
                </div>
            </div>
        </section>

        <section id="sessao_acoes">
            <div class="btn_novo_func" onclick="abrirModal()">
                <i class="fa-solid fa-user-plus"></i>
                <span>Novo funcionário</span>
            </div>

            <div class="busca_container">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="input_busca" placeholder="Pesquisar funcionário...">
            </div>
        </section>

        <section id="lista_usuarios_cards" class="grid_funcionarios">
            </section>

        <div id="modal_funcionario" class="modal_overlay">
            <div class="modal_content">
                <div class="modal_header">
                    <h3>Cadastrar Funcionário</h3>
                    <span id="fechar_modal" onclick="fecharModal()">&times;</span>
                </div>

        <form id="form_usuario" enctype="multipart/form-data">
            <div class="input_group">
                <label>Foto de Perfil</label>
                <input type="file" id="foto" name="foto" accept="image/*">
            </div>

            <div class="input_group">
                <label>Nome Completo</label>
                <input type="text" id="nome" name="nome" required placeholder="Nome do funcionário">
            </div>

            <div class="input_group">
                <label>E-mail Corporativo (Login)</label>
                <input type="email" id="login" name="login" required placeholder="email@empresa.com">
            </div>

            <div class="input_group">
                <label>Senha Provisória</label>
                <input type="password" id="senha" name="senha" required placeholder="Digite a senha">
            </div>

            <div class="input_group">
                <label>Nível de Acesso</label>
                <select id="nivel" name="nivel">
                    <option value="admin">Administrador</option>
                    <option value="comum" selected>Comum / Garçom</option>
                </select>
            </div>

            <button type="submit" class="btn_finalizar">Finalizar Cadastro</button>
        </form>
            </div>
        </div>
    </main>

    <script src="<?php echo htmlspecialchars(url_da_aplicacao('recursos/js/scripts_painel.js')); ?>"></script>
    <script>
        window.__APP = { urlLogin: <?php echo json_encode(url_da_aplicacao('index.php'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?> };
    </script>
    <script src="<?php echo htmlspecialchars(url_da_aplicacao('recursos/js/cliente_http.js')); ?>"></script>
    <script>
        const URL_API_USER = <?php echo json_encode(url_da_aplicacao('apis/usuarios.php'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?>;
let listaUsuarios = [];

// 1. LISTAR USUÁRIOS
async function listarUsuarios() {
    const container = document.getElementById('lista_usuarios_cards');
    if (container) container.innerHTML = '<p style="padding:16px;color:var(--cinza-texto)">Carregando…</p>';
    const res = await apiFetch(URL_API_USER + '?acao=listar_usuarios');
    if (!res.success) {
        if (container) container.innerHTML = '<p style="padding:16px;color:var(--vermelho-excluir)">' + (res.message || 'Erro ao carregar.') + '</p>';
        return;
    }
    listaUsuarios = res.dados && res.dados.usuarios ? res.dados.usuarios : [];
    renderizarCards(listaUsuarios);
    atualizarResumo(listaUsuarios);
}

// 2. RENDERIZAR CARDS
function renderizarCards(usuarios) {
    const container = document.getElementById('lista_usuarios_cards');
    if (!container) return;

    if (!usuarios.length) {
        container.innerHTML = '<p style="padding:20px;color:var(--cinza-texto)">Nenhum funcionário cadastrado.</p>';
        return;
    }
    container.innerHTML = usuarios.map(u => {
        const isAtivo = u.ativo == 1;
        // Se url_imagem vier do PHP com o caminho, usamos ele, senão o padrão
        const foto = u.url_imagem ? u.url_imagem : '../../recursos/uploads/sem_foto.svg';
        
        return `
        <div class="card_funcionario_item ${isAtivo ? '' : 'usuario_inativo'}">
            <div class="card_topo">
                <img src="${foto}" class="foto_perfil" onerror="this.src='../../recursos/uploads/sem_foto.svg'">
                <div class="ponto_status ${isAtivo ? 'online' : 'offline'}"></div>
                <button class="btn_edit_card" onclick="abrirEdicao(${u.id_usuario})">
                    <i class="fa-solid fa-pencil"></i>
                </button>
            </div>
            <div class="card_corpo">
                <h4>${u.nome}</h4>
                <p class="nivel_txt">${u.nivel ? u.nivel.toUpperCase() : 'NÃO DEFINIDO'}</p>
                <span class="badge_status ${isAtivo ? 'badge_verde' : 'badge_vermelha'}">
                    ${isAtivo ? 'Ativo' : 'Inativo'}
                </span>
                
                <div class="contato_info">
                    <p><i class="fa-regular fa-envelope"></i> ${u.login}</p>
                    <p><i class="fa-solid fa-phone"></i> (11) 99999-9999</p>
                </div>

                <button class="btn_status_toggle ${isAtivo ? 'btn_desativar' : 'btn_ativar'}" 
                        onclick="alternarStatusUsuario(${u.id_usuario})">
                    ${isAtivo ? 'Desativar Conta' : 'Reativar Conta'}
                </button>
            </div>
        </div>
        `;
    }).join('');
}

// 3. ATUALIZAR RESUMO NUMÉRICO
function atualizarResumo(usuarios) {
    const totalGeral = document.getElementById('total_geral');
    const totalAtivos = document.getElementById('total_ativos');
    const totalInativos = document.getElementById('total_inativos');

    if (totalGeral) totalGeral.innerText = usuarios.length;
    if (totalAtivos) totalAtivos.innerText = usuarios.filter(u => u.ativo == 1).length;
    if (totalInativos) totalInativos.innerText = usuarios.filter(u => u.ativo == 0).length;
}

// 4. ALTERNAR STATUS (ATIVAR/REATIVAR) - Via PUT (JSON)
async function alternarStatusUsuario(id) {
    if (!confirm('Deseja alterar o status deste funcionário?')) return;

    const res = await apiFetch(URL_API_USER + '?acao=alterar_status', {
        method: 'PUT',
        body: JSON.stringify({ id_usuario: id })
    });
    if (res.success) {
        listarUsuarios();
    } else {
        alert('Erro: ' + res.message);
    }
}

// 5. CADASTRO - Corrigido para suportar FOTO (FormData)
document.getElementById('form_usuario').onsubmit = async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);

    const res = await apiFetch(URL_API_USER + '?acao=cadastrar_usuario', {
        method: 'POST',
        body: formData
    });

    if (res.success) {
        alert('Funcionário cadastrado com sucesso!');
        fecharModal();
        listarUsuarios();
        e.target.reset();
    } else {
        alert('Erro: ' + res.message);
    }
};

// FILTRO DE BUSCA
document.getElementById('input_busca').addEventListener('input', (e) => {
    const busca = e.target.value.toLowerCase();
    const filtrados = listaUsuarios.filter(u => 
        u.nome.toLowerCase().includes(busca) || u.login.toLowerCase().includes(busca)
    );
    renderizarCards(filtrados);
});

// MODAL CONTROLS
function abrirModal() { document.getElementById('modal_funcionario').style.display = 'flex'; }
function fecharModal() { document.getElementById('modal_funcionario').style.display = 'none'; }

// Inicializar
listarUsuarios();
    </script>
</body>
</html>