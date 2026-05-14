<?php
require_once dirname(__DIR__, 2) . '/configuracao/verificar_sessao.php';
require_once dirname(__DIR__, 2) . '/configuracao/caminhos_da_aplicacao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_da_aplicacao('recursos/css/estilos.css')); ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js" crossorigin="anonymous"></script>
    <title>Sistema de Gerenciamento - Cardápio</title>
</head>
<body>

    <?php include dirname(__DIR__, 2) . '/componentes/nav.php'; ?>

    <main id="main_cardapio" class="w-100">
        <div class="container-fluid px-2 px-md-3 pb-4">
            <header id="header_cardapio" class="px-1">
                <h2>Monte aqui o seu cardápio</h2>
                <p>Gerencie categorias, produtos e adicionais do seu estabelecimento.</p>
            </header>

            <section class="conteudo_gerenciamento_desktop">
                <div class="row g-3 align-items-stretch">
                    <div class="col-12 col-lg-6">
                        <div class="bloco_secao h-100">
                            <div class="cabecalho_secao">
                                <h3>Categoria</h3>
                                <button class="btn_adicionar" id="btn_nova_categoria" type="button" title="Nova Categoria">
                                    <i class="fa-solid fa-plus"></i> <span>Categoria</span>
                                </button>
                            </div>
                            <ul class="lista_categorias"></ul>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="bloco_secao h-100">
                            <div class="cabecalho_secao">
                                <h3>Adicionais extras</h3>
                                <button class="btn_adicionar" id="btn_novo_adicional" type="button" title="Novo Adicional">
                                    <i class="fa-solid fa-plus"></i> <span>Adicional</span>
                                </button>
                            </div>
                            <div class="grid_adicionais" id="lista_adicionais"></div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-0">
                    <div class="col-12">
                        <div class="bloco_secao secao_produtos_desktop">
                            <div class="cabecalho_secao">
                                <h3>Produtos do cardápio</h3>
                                <button class="btn_adicionar" id="btn_novo_produto" type="button" title="Novo Produto">
                                    <i class="fa-solid fa-plus"></i> <span>Novo produto</span>
                                </button>
                            </div>
                            <div class="grid_produtos" id="lista_produtos"></div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <div class="fundo_modal" id="modal_categoria">
        <div class="caixa_modal">
            <div class="cabecalho_modal">
                <h3 id="titulo_modal_cat">Nova Categoria</h3>
                <button class="btn_fechar_modal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="form_categoria" class="corpo_modal">
                <input type="hidden" id="id_categoria_edit" name="id_categoria">
                <div class="grupo_input">
                    <label for="nome_categoria_input">Nome da Categoria</label>
                    <input type="text" id="nome_categoria_input" name="nome_categoria" placeholder="Ex: Bebidas" required>
                </div>
                <div class="rodape_modal">
                    <button type="button" class="btn_cancelar">Cancelar</button>
                    <button type="submit" class="btn_salvar">Salvar Categoria</button>
                </div>
            </form>
        </div>
    </div>

    <div class="fundo_modal" id="modal_produto">
        <div class="caixa_modal caixa_modal_grande">
            <div class="cabecalho_modal">
                <h3 id="titulo_modal_prod">Novo Produto</h3>
                <button class="btn_fechar_modal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="form_produto" class="corpo_modal" enctype="multipart/form-data">
                <input type="hidden" id="id_produto_edit" name="id_item_cardapio">
                <input type="hidden" id="imagem_atual_input" name="imagem_atual">
                
                <div class="linha_inputs_duplos">
                    <div class="grupo_input">
                        <label>Categoria</label>
                        <select id="categoria_produto_input" name="id_categoria" required>
                            <option value="">Carregando...</option>
                        </select>
                    </div>
                    <div class="grupo_input">
                        <label>Nome do Produto</label>
                        <input type="text" name="nome_item" id="nome_item_input" required placeholder="Ex: X-Salada">
                    </div>
                </div>

                <div class="linha_inputs_duplos">
                    <div class="grupo_input">
                        <label>Preço (R$)</label>
                        <input type="number" step="0.01" name="valor_item" id="valor_item_input" required placeholder="0.00">
                    </div>
                    <div class="grupo_input">
                        <label>Foto do Produto</label>
                        <input type="file" name="foto_item" id="foto_item_input" accept="image/*">
                    </div>
                </div>

                <div class="grupo_input">
                    <label>Descrição/Ingredientes</label>
                    <textarea name="descricao_item" id="descricao_item_input" rows="3" placeholder="Ex: Pão, carne 180g, queijo..."></textarea>
                </div>

                <div class="rodape_modal">
                    <button type="button" class="btn_cancelar">Cancelar</button>
                    <button type="submit" class="btn_salvar">Salvar Produto</button>
                </div>
            </form>
        </div>
    </div>

    <div class="fundo_modal" id="modal_adicional">
        <div class="caixa_modal">
            <div class="cabecalho_modal">
                <h3 id="titulo_modal_adic">Novo Adicional</h3>
                <button class="btn_fechar_modal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="form_adicional" class="corpo_modal">
                <input type="hidden" id="id_adicional_edit" name="id_adicional">
                <div class="grupo_input">
                    <label>Categoria Vinculada</label>
                    <select id="categoria_adicional_input" name="id_categoria" required>
                        <option value="">Carregando...</option>
                    </select>
                </div>
                <div class="grupo_input">
                    <label>Nome do Adicional</label>
                    <input type="text" name="nome_adicional" id="nome_adicional_input" required placeholder="Ex: Bacon Extra">
                </div>
                <div class="grupo_input">
                    <label>Valor (R$)</label>
                    <input type="number" step="0.01" name="valor_adicional" id="valor_adicional_input" required placeholder="2.50">
                </div>
                <div class="rodape_modal">
                    <button type="button" class="btn_cancelar">Cancelar</button>
                    <button type="submit" class="btn_salvar">Salvar Adicional</button>
                </div>
            </form>
        </div>
    </div>

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

        /** Espelha url_publica_imagem() do PHP para caminhos vindos da API. */
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

        // --- FUNÇÕES DE INTERFACE ---
        function abrirModal(id) {
            document.getElementById(id).classList.add('modal_ativo');
        }

        function fecharModais() {
            document.querySelectorAll('.fundo_modal').forEach(modal => {
                modal.classList.remove('modal_ativo');
            });
        }

        // --- LISTAGEM DE CATEGORIAS ---
        async function listarCategorias() {
            const listaUl = document.querySelector('.lista_categorias');
            const selectCatProd = document.getElementById('categoria_produto_input');
            const selectCatAdic = document.getElementById('categoria_adicional_input');
            listaUl.innerHTML = '<p style="padding:12px;font-size:13px;color:var(--cinza-texto)">Carregando…</p>';

            const result = await apiFetch(`${API_CATEGORIAS}?acao=listar_categorias`);
            if (!result.success) {
                listaUl.innerHTML = '<p style="padding:12px;color:var(--vermelho-excluir)">' + (result.message || 'Erro ao carregar.') + '</p>';
                return;
            }
            const cats = (result.dados && result.dados.categorias) ? result.dados.categorias : [];
            if (!cats.length) {
                listaUl.innerHTML = '<p style="padding:12px;font-size:13px;color:var(--cinza-texto)">Nenhuma categoria cadastrada.</p>';
                if (selectCatProd) selectCatProd.innerHTML = '<option value="">Selecione…</option>';
                if (selectCatAdic) selectCatAdic.innerHTML = '<option value="">Selecione…</option>';
                return;
            }
            listaUl.innerHTML = cats.map(cat => `
                        <li class="item_categoria">
                            <div class="info_categoria">
                                <i class="fa-solid fa-tags" style="color: var(--cor-principal);"></i>
                                <span class="nome_categoria">${cat.nome_categoria}</span>
                            </div>
                            <div class="acoes_botoes">
                                <button type="button" class="btn_icone_editar" onclick="prepararEdicaoCat(${cat.id_categoria}, '${String(cat.nome_categoria).replace(/'/g, "\\'")}')">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button type="button" class="btn_icone_excluir" onclick="deletarCat(${cat.id_categoria})">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </li>
                    `).join('');

            const options = '<option value="">Selecione...</option>' +
                cats.map(c => `<option value="${c.id_categoria}">${c.nome_categoria}</option>`).join('');
            if (selectCatProd) selectCatProd.innerHTML = options;
            if (selectCatAdic) selectCatAdic.innerHTML = options;
        }

        // --- LISTAGEM DE ADICIONAIS ---
        async function listarAdicionais() {
            const listaDiv = document.getElementById('lista_adicionais');
            listaDiv.innerHTML = '<p style="padding:12px;font-size:13px;color:var(--cinza-texto)">Carregando…</p>';
            const result = await apiFetch(`${API_ADICIONAIS}?acao=listar_adicionais`);
            if (!result.success) {
                listaDiv.innerHTML = '<p style="padding:12px;color:var(--vermelho-excluir)">' + (result.message || 'Erro.') + '</p>';
                return;
            }
            const dados = (result.dados && result.dados.adicionais) ? result.dados.adicionais : [];
            if (!dados.length) {
                listaDiv.innerHTML = '<p style="padding:12px;color:var(--cinza-texto)">Nenhum adicional cadastrado.</p>';
                return;
            }
            listaDiv.innerHTML = dados.map(adic => `
                        <div class="card_item_gerenciamento" style="border: 1px solid #ddd; padding: 10px; margin-bottom: 5px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                            <div class="info_item">
                                <strong>${adic.nome_adicional}</strong> <br>
                                <small>${adic.nome_categoria || 'Sem categoria'}</small>
                                <p style="color: green; margin: 0;">R$ ${parseFloat(adic.valor_adicional || 0).toFixed(2)}</p>
                            </div>
                            <div class="acoes_botoes">
                                <button type="button" class="btn_icone_editar" onclick="prepararEdicaoAdicional(${adic.id_adicional}, '${String(adic.nome_adicional).replace(/'/g, "\\'")}', ${adic.valor_adicional}, ${adic.id_categoria})">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button type="button" class="btn_icone_excluir" onclick="deletarAdicional(${adic.id_adicional})">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    `).join('');
        }

        // --- LISTAGEM DE PRODUTOS ---
        async function listarProdutos() {
            const grid = document.getElementById('lista_produtos');
            grid.innerHTML = '<p style="padding:12px;font-size:13px;color:var(--cinza-texto)">Carregando…</p>';
            const result = await apiFetch(`${API_PRODUTOS}?acao=listar_itens`);
            if (!result.success) {
                grid.innerHTML = '<p style="padding:12px;color:var(--vermelho-excluir)">' + (result.message || 'Erro.') + '</p>';
                return;
            }
            const itens = (result.dados && result.dados.itens) ? result.dados.itens : [];
            if (!itens.length) {
                grid.innerHTML = '<p style="padding:12px;color:var(--cinza-texto)">Nenhum produto cadastrado.</p>';
                return;
            }
            grid.innerHTML = itens.map(prod => {
                const foto = urlFotoProduto(prod.url_imagem);
                const prodDados = JSON.stringify(prod).replace(/'/g, "&apos;");
                return `
                        <div class="card_produto">
                            <img src="${foto}" alt="${prod.nome}" class="card_img">
                            <div class="info_produto">
                                <h4 class="card_titulo">${prod.nome}</h4>
                                <p class="card_descricao">${prod.descricao || 'Sem descrição cadastrada.'}</p>
                                <strong class="card_preco">R$ ${parseFloat(prod.preco || 0).toFixed(2)}</strong>
                            </div>
                            <div class="acoes_card">
                                <button type="button" onclick='prepararEdicaoProduto(${prodDados})' class="btn_editar_texto"><i class="fa-solid fa-pen-to-square"></i> Editar</button>
                                <button type="button" onclick="deletarProduto(${prod.id_item})" class="btn_excluir_texto"><i class="fa-solid fa-trash-can"></i> Excluir</button>
                            </div>
                        </div>`;
            }).join('');
        }

        // --- PREPARAÇÃO DE EDIÇÃO ---
        window.prepararEdicaoProduto = (prod) => {
            document.getElementById('id_produto_edit').value = prod.id_item;
            document.getElementById('nome_item_input').value = prod.nome;
            document.getElementById('valor_item_input').value = prod.preco;
            document.getElementById('categoria_produto_input').value = prod.id_categoria;
            document.getElementById('descricao_item_input').value = prod.descricao;
            document.getElementById('imagem_atual_input').value = prod.url_imagem;
            document.getElementById('titulo_modal_prod').innerText = "Editar Produto";
            abrirModal('modal_produto');
        };

        window.prepararEdicaoCat = (id, nome) => {
            document.getElementById('id_categoria_edit').value = id;
            document.getElementById('nome_categoria_input').value = nome;
            document.getElementById('titulo_modal_cat').innerText = "Editar Categoria";
            abrirModal('modal_categoria');
        };

        window.prepararEdicaoAdicional = (id, nome, valor, catId) => {
            document.getElementById('id_adicional_edit').value = id;
            document.getElementById('nome_adicional_input').value = nome;
            document.getElementById('valor_adicional_input').value = valor;
            document.getElementById('categoria_adicional_input').value = catId;
            document.getElementById('titulo_modal_adic').innerText = "Editar Adicional";
            abrirModal('modal_adicional');
        };

        // --- EXCLUSÃO ---
        window.deletarProduto = async (id) => {
            if (!confirm('Excluir produto?')) return;
            const data = await apiFetch(`${API_PRODUTOS}?acao=deletar_item&id=${id}`, { method: 'DELETE' });
            if (data.success) listarProdutos();
            else alert(data.message || 'Erro ao excluir.');
        };

        window.deletarCat = async (id) => {
            if (!confirm('Excluir categoria? Isso pode afetar produtos e adicionais vinculados.')) return;
            const data = await apiFetch(`${API_CATEGORIAS}?acao=deletar_categoria&id=${id}`, { method: 'DELETE' });
            if (data.success) { listarCategorias(); listarProdutos(); listarAdicionais(); }
            else alert(data.message || 'Erro.');
        };

        window.deletarAdicional = async (id) => {
            if (!confirm('Excluir adicional?')) return;
            const data = await apiFetch(`${API_ADICIONAIS}?acao=deletar_adicional&id=${id}`, { method: 'DELETE' });
            if (data.success) listarAdicionais();
            else alert(data.message || 'Erro.');
        };

        // --- SUBMITS ---
        document.addEventListener('DOMContentLoaded', () => {
            listarCategorias();
            listarAdicionais();
            listarProdutos();

            // Botões de abertura Novo
            document.getElementById('btn_novo_produto').onclick = () => {
                document.getElementById('form_produto').reset();
                document.getElementById('id_produto_edit').value = '';
                document.getElementById('imagem_atual_input').value = '';
                document.getElementById('titulo_modal_prod').innerText = "Novo Produto";
                abrirModal('modal_produto');
            };

            document.getElementById('btn_nova_categoria').onclick = () => {
                document.getElementById('form_categoria').reset();
                document.getElementById('id_categoria_edit').value = '';
                document.getElementById('titulo_modal_cat').innerText = "Nova Categoria";
                abrirModal('modal_categoria');
            };

            document.getElementById('btn_novo_adicional').onclick = () => {
                document.getElementById('form_adicional').reset();
                document.getElementById('id_adicional_edit').value = '';
                document.getElementById('titulo_modal_adic').innerText = "Novo Adicional";
                abrirModal('modal_adicional');
            };

            document.querySelectorAll('.btn_fechar_modal, .btn_cancelar').forEach(btn => {
                btn.onclick = fecharModais;
            });

            // SUBMIT PRODUTO (POST para upload de arquivo)
            document.getElementById('form_produto').onsubmit = async (e) => {
                e.preventDefault();
                const id = document.getElementById('id_produto_edit').value;
                const acao = id ? 'editar_item' : 'cadastrar_item';
                const formData = new FormData(e.target);
                
                const data = await apiFetch(`${API_PRODUTOS}?acao=${acao}`, { method: 'POST', body: formData });
                if (data.success) { fecharModais(); listarProdutos(); }
                else alert(data.message || 'Erro ao salvar produto');
            };

            // SUBMIT CATEGORIA (Usando POST/JSON para facilitar pro PHP)
            document.getElementById('form_categoria').onsubmit = async (e) => {
                e.preventDefault();
                const id = document.getElementById('id_categoria_edit').value;
                const nome = document.getElementById('nome_categoria_input').value;
                const acao = id ? 'editar_categoria' : 'cadastrar_categoria';
                
                const data = await apiFetch(`${API_CATEGORIAS}?acao=${acao}`, {
                    method: 'POST',
                    body: JSON.stringify({ id_categoria: id, nome_categoria: nome })
                });
                if (data.success) { fecharModais(); listarCategorias(); listarAdicionais(); }
                else alert(data.message || 'Erro ao salvar categoria.');
            };

            // SUBMIT ADICIONAL (POST/JSON)
            document.getElementById('form_adicional').onsubmit = async (e) => {
                e.preventDefault();
                const id = document.getElementById('id_adicional_edit').value;
                const acao = id ? 'editar_adicional' : 'cadastrar_adicional';
                const formData = new FormData(e.target);
                const obj = Object.fromEntries(formData.entries());

                const data = await apiFetch(`${API_ADICIONAIS}?acao=${acao}`, {
                    method: 'POST',
                    body: JSON.stringify(obj)
                });
                if (data.success) { fecharModais(); listarAdicionais(); }
                else alert(data.message || 'Erro ao salvar adicional.');
            };
        });
    </script>
</body>
</html>