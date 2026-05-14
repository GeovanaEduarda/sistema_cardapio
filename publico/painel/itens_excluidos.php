<?php
require_once dirname(__DIR__, 2) . '/configuracao/verificar_sessao.php';
require_once dirname(__DIR__, 2) . '/configuracao/caminhos_da_aplicacao.php';
?><!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_da_aplicacao('recursos/css/estilos.css')); ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js" crossorigin="anonymous"></script>
    <title>Lixeira — Gerenciamento de cardápio</title>
    <style>
        .btn_lixeira_definitiva {
            background: transparent;
            border: none;
            color: var(--vermelho-excluir);
            padding: 8px 10px;
            cursor: pointer;
            border-radius: 8px;
            line-height: 1;
        }
        .btn_lixeira_definitiva:hover { background: var(--vermelho-claro); color: #b91c1c; }
        .btn_lixeira_definitiva:focus-visible { outline: 2px solid var(--vermelho-excluir); outline-offset: 2px; }
        .acoes_lixeira { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
        .badge_excluido {
            background-color: var(--vermelho-claro);
            color: var(--vermelho-excluir);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 5px;
            display: inline-block;
        }
        .btn_restaurar {
            background-color: var(--cor-principal);
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: 0.2s;
        }
        .btn_restaurar:hover { background-color: var(--cor-principal-destaque); color: #fff; }
    </style>
</head>
<body>

    <?php include dirname(__DIR__, 2) . '/componentes/nav.php'; ?>

    <main id="main_cardapio" class="w-100">
        <div class="container-fluid px-2 px-md-3 pb-4">
            <header id="header_cardapio" class="px-1">
                <h2><i class="fa-solid fa-trash-can" style="color: var(--laranja);" aria-hidden="true"></i> Itens excluídos</h2>
                <p>Visualize, restaure ou remova permanentemente itens da lixeira.</p>
            </header>

            <section class="conteudo_gerenciamento_desktop">
                <div class="row g-3 align-items-stretch">
                    <div class="col-12 col-lg-6">
                        <div class="bloco_secao h-100">
                            <div class="cabecalho_secao">
                                <h3>Categorias excluídas</h3>
                            </div>
                            <ul class="lista_categorias" id="lista_categorias_excluidas"></ul>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="bloco_secao h-100">
                            <div class="cabecalho_secao">
                                <h3>Adicionais excluídos</h3>
                            </div>
                            <div class="grid_adicionais" id="lista_adicionais_excluidos"></div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-0">
                    <div class="col-12">
                        <div class="bloco_secao secao_produtos_desktop">
                            <div class="cabecalho_secao">
                                <h3>Produtos excluídos</h3>
                            </div>
                            <div class="grid_produtos" id="lista_produtos_excluidos"></div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script src="<?php echo htmlspecialchars(url_da_aplicacao('recursos/js/scripts_painel.js')); ?>"></script>
    <script>
        window.__APP = { urlLogin: <?php echo json_encode(url_da_aplicacao('index.php'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?> };
    </script>
    <script src="<?php echo htmlspecialchars(url_da_aplicacao('recursos/js/cliente_http.js')); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
        const API_CATEGORIAS = <?php echo json_encode(url_da_aplicacao('apis/categorias.php'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?>;
        const API_ADICIONAIS = <?php echo json_encode(url_da_aplicacao('apis/adicionais.php'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?>;
        const API_PRODUTOS = <?php echo json_encode(url_da_aplicacao('apis/itens_do_cardapio.php'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?>;
        const URL_SEM_FOTO = <?php echo json_encode(url_publica_imagem(null), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?>;

        function urlFotoProduto(guardado) {
            let g = String(guardado || '').trim();
            if (!g) return URL_SEM_FOTO;
            if (/^https?:\/\//i.test(g)) return g;
            if (g.charAt(0) === '/') return g;
            g = g.replace(/^\.\.\//g, '').replace(/\\/g, '/').replace(/^\//, '');
            if (g.indexOf('assets/uploads/') === 0) {
                g = 'recursos/uploads/' + g.slice('assets/uploads/'.length);
            }
            const base = <?php echo json_encode(rtrim(url_da_aplicacao(''), '/'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?>;
            const path = (base ? base + '/' : '/') + g;
            return path.indexOf('//') === 0 ? path.replace('//', '/') : path;
        }

        async function listarCategoriasExcluidas() {
            const listaUl = document.getElementById('lista_categorias_excluidas');
            listaUl.innerHTML = '<p style="padding:12px;color:var(--cinza-texto)">Carregando…</p>';
            const result = await apiFetch(`${API_CATEGORIAS}?acao=listar_categorias&status=excluidos`);
            if (!result.success) {
                listaUl.innerHTML = '<p style="padding:20px;color:var(--vermelho-excluir)">' + (result.message || 'Erro.') + '</p>';
                return;
            }
            const categorias = (result.dados && result.dados.categorias) ? result.dados.categorias : [];
            if (categorias.length > 0) {
                listaUl.innerHTML = categorias.map(cat => `
                        <li class="item_categoria">
                            <div class="info_categoria">
                                <i class="fa-solid fa-tag" aria-hidden="true"></i>
                                <span class="nome_categoria">${cat.nome_categoria}</span>
                            </div>
                            <div class="acoes_lixeira">
                                <button type="button" class="btn_restaurar" onclick="restaurarItem('categoria', ${cat.id_categoria})">
                                    <i class="fa-solid fa-rotate-left"></i> Restaurar
                                </button>
                                <button type="button" class="btn_lixeira_definitiva" title="Excluir definitivamente" aria-label="Excluir categoria definitivamente" onclick="excluirDefinitivo('categoria', ${cat.id_categoria})">
                                    <i class="fa-solid fa-trash-alt" aria-hidden="true"></i>
                                </button>
                            </div>
                        </li>
                    `).join('');
            } else {
                listaUl.innerHTML = '<p style="padding:20px; font-size:13px; color:var(--cinza-texto)">Nenhuma categoria na lixeira.</p>';
            }
        }

        async function listarAdicionaisExcluidos() {
            const listaDiv = document.getElementById('lista_adicionais_excluidos');
            listaDiv.innerHTML = '<p style="padding:12px;color:var(--cinza-texto)">Carregando…</p>';
            const result = await apiFetch(`${API_ADICIONAIS}?acao=listar_adicionais&status=excluidos`);
            if (!result.success) {
                listaDiv.innerHTML = '<p style="padding:20px;color:var(--vermelho-excluir)">' + (result.message || 'Erro.') + '</p>';
                return;
            }
            const dados = (result.dados && result.dados.adicionais) ? result.dados.adicionais : [];
            if (dados.length > 0) {
                listaDiv.innerHTML = dados.map(adic => `
                        <div class="card_item_gerenciamento" style="border: 1px solid var(--cor-bordas); padding: 15px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; background: white; gap: 10px;">
                            <div class="info_item" style="min-width:0;">
                                <span class="badge_excluido">EXCLUÍDO</span><br>
                                <strong>${adic.nome_adicional}</strong> <br>
                                <small>${adic.nome_categoria || 'Sem categoria'}</small>
                                <p style="color: var(--laranja); font-weight:bold; margin: 0;">R$ ${parseFloat(adic.valor_adicional || 0).toFixed(2)}</p>
                            </div>
                            <div class="acoes_lixeira">
                                <button type="button" class="btn_restaurar" onclick="restaurarItem('adicional', ${adic.id_adicional})" title="Restaurar">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </button>
                                <button type="button" class="btn_lixeira_definitiva" title="Excluir definitivamente" aria-label="Excluir adicional definitivamente" onclick="excluirDefinitivo('adicional', ${adic.id_adicional})">
                                    <i class="fa-solid fa-trash-alt" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    `).join('');
            } else {
                listaDiv.innerHTML = '<p style="grid-column: 1/-1; padding:20px; color:var(--cinza-texto)">Nenhum adicional na lixeira.</p>';
            }
        }

        async function listarProdutosExcluidos() {
            const grid = document.getElementById('lista_produtos_excluidos');
            grid.innerHTML = '<p style="padding:12px;color:var(--cinza-texto)">Carregando…</p>';
            const result = await apiFetch(`${API_PRODUTOS}?acao=listar_itens&status=excluidos`);
            if (!result.success) {
                grid.innerHTML = '<p style="padding:20px;color:var(--vermelho-excluir)">' + (result.message || 'Erro.') + '</p>';
                return;
            }
            const itens = (result.dados && result.dados.itens) ? result.dados.itens : [];
            if (itens.length > 0) {
                grid.innerHTML = itens.map(prod => {
                    const foto = urlFotoProduto(prod.url_imagem);
                    return `
                        <div class="card_produto" style="opacity: 0.92">
                            <img src="${foto}" alt="${String(prod.nome).replace(/"/g, '&quot;')}" class="card_img">
                            <div class="info_produto">
                                <span class="badge_excluido">PRODUTO EXCLUÍDO</span>
                                <h4 class="card_titulo">${prod.nome}</h4>
                                <p class="card_descricao">${prod.descricao || 'Sem descrição.'}</p>
                                <strong class="card_preco">R$ ${parseFloat(prod.preco || 0).toFixed(2)}</strong>
                            </div>
                            <div class="acoes_card" style="display:flex;flex-wrap:wrap;gap:8px;padding:10px;border-top:1px solid var(--cor-bordas);">
                                <button type="button" onclick="restaurarItem('produto', ${prod.id_item})" class="btn_restaurar" style="flex:1;max-width:150px;padding:10px;">
                                    <i class="fa-solid fa-rotate-left"></i> Restaurar
                                </button>
                                <button type="button" class="btn_lixeira_definitiva" style="flex:0 0 auto;align-self:center; min-width:150px; background-color:#F8D7DA;" title="Excluir definitivamente" aria-label="Excluir produto definitivamente" onclick="excluirDefinitivo('produto', ${prod.id_item})">
                                    <i class="fa-solid fa-trash-alt fa-lg" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>`;
                }).join('');
            } else {
                grid.innerHTML = '<p style="padding:20px; color:var(--cinza-texto)">Nenhum produto na lixeira.</p>';
            }
        }

        async function restaurarItem(tipo, id) {
            if (!confirm('Deseja restaurar este ' + tipo + '?')) return;

            let url = '';
            if (tipo === 'categoria') url = `${API_CATEGORIAS}?acao=restaurar_categoria&id=${id}`;
            if (tipo === 'adicional') url = `${API_ADICIONAIS}?acao=restaurar_adicional&id=${id}`;
            if (tipo === 'produto') url = `${API_PRODUTOS}?acao=restaurar_item&id=${id}`;

            const data = await apiFetch(url, { method: 'POST' });
            if (data.success) {
                alert('Item restaurado com sucesso!');
                listarCategoriasExcluidas();
                listarAdicionaisExcluidos();
                listarProdutosExcluidos();
            } else {
                alert('Erro ao restaurar: ' + (data.message || ''));
            }
        }

        async function excluirDefinitivo(tipo, id) {
            const msg = tipo === 'categoria'
                ? 'Remover PERMANENTEMENTE esta categoria? Itens vinculados podem impedir a exclusão até serem apagados da lixeira.'
                : 'Remover PERMANENTEMENTE da base de dados? Não será possível recuperar.';
            if (!confirm(msg)) return;

            let url = '';
            if (tipo === 'categoria') url = `${API_CATEGORIAS}?acao=excluir_definitivo&id=${id}`;
            if (tipo === 'adicional') url = `${API_ADICIONAIS}?acao=excluir_definitivo&id=${id}`;
            if (tipo === 'produto') url = `${API_PRODUTOS}?acao=excluir_definitivo&id=${id}`;

            const data = await apiFetch(url, { method: 'POST' });
            if (data.success) {
                listarCategoriasExcluidas();
                listarAdicionaisExcluidos();
                listarProdutosExcluidos();
            } else {
                alert(data.message || 'Não foi possível excluir definitivamente.');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            listarCategoriasExcluidas();
            listarAdicionaisExcluidos();
            listarProdutosExcluidos();
        });
    </script>
</body>
</html>