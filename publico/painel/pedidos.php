<?php
require_once dirname(__DIR__, 2) . '/configuracao/verificar_sessao.php';
require_once dirname(__DIR__, 2) . '/configuracao/caminhos_da_aplicacao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos — Sistema de Gerenciamento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_da_aplicacao('recursos/css/estilos.css')); ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js" crossorigin="anonymous"></script>
    <style>
        #main_pedidos { flex: 1; padding: 2rem; }
        #header_pedidos { margin-bottom: 2rem; }
        
        #section_filtro { 
            display: flex; 
            gap: 0.75rem; 
            background: #eee; 
            padding: 0.4rem; 
            border-radius: 12px;
            width: fit-content;
        }
        
        .filtro_header { 
            padding: 0.5rem 1.25rem; 
            border-radius: 8px; 
            cursor: pointer; 
            border: none; 
            background: transparent; 
            font-size: 0.875rem; 
            font-weight: 600;
            color: #666;
            transition: all 0.2s ease;
        }
        
        .filtro_header:hover { background: rgba(255,255,255,0.5); }
        
        .filtro_header.active { 
            background: #fff; 
            color: var(--cor-principal); 
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .btn-criar-pedido {
            font-weight: 700;
            box-shadow: 0 8px 20px rgba(234, 119, 0, 0.4);
            background-color: #ea7700 !important;
            border: none !important;
            color: #fff !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-criar-pedido:hover { transform: scale(1.05); box-shadow: 0 10px 25px rgba(234, 119, 0, 0.5); }
        
        /* Modal Fixes */
        .modal-backdrop { z-index: 1040 !important; }
        .modal { z-index: 1050 !important; }
        .modal-content { border: none; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .modal-header { border-bottom: 1px solid #f0f0f0; padding: 1.5rem; }
        .modal-body { padding: 1.5rem; }
        .modal-footer { border-top: 1px solid #f0f0f0; padding: 1.25rem; }

        /* Estilos para Impressão do Ticket (Cozinha) */
        #print_section { display: none; }
        @media print {
            body * { visibility: hidden; }
            #print_section, #print_section * { visibility: visible; }
            #print_section {
                display: block !important;
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                background: #fff;
                color: #000;
                font-family: 'Courier New', Courier, monospace;
                padding: 10px;
            }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <!-- Container oculto para impressão -->
    <div id="print_section"></div>

    <?php include dirname(__DIR__, 2) . '/componentes/nav.php'; ?>

    <main id="main_pedidos">
        <header  class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div id="header_pedidos" class="d-flex align-items-center gap-4">
                <h2 class="h4 mb-0 fw-bold" style="color: #333;">Pedidos ativos</h2>
                <section id="section_filtro">
                    <div class="filtro_header active" data-filter="todos">Todos</div>
                    <div class="filtro_header" data-filter="mesa">Mesa</div>
                    <div class="filtro_header" data-filter="balcao">Balcão</div>
                </section>
            </div>
            <button type="button" class="btn btn-lg rounded-circle btn-criar-pedido position-fixed bottom-0 end-0 m-4 d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#modal_novo_pedido" style="width:65px;height:65px;z-index:1030;"><i class="fa-solid fa-plus fs-4" aria-hidden="true"></i></button>
        </header>

        <section id="container_cards_pedidos" class="mt-3">
            <p class="text-secondary py-4 text-center">Carregando pedidos…</p>
        </section>
    </main>

    <!-- Modal: novo pedido (apenas Mesa ou Balcão) -->
    <div class="modal fade" id="modal_novo_pedido" tabindex="-1" aria-labelledby="titulo_modal_novo_pedido" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="titulo_modal_novo_pedido">Novo pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Escolha a origem e informe a mesa ou identificador. Depois, adicione itens no card do pedido.</p>
                    <div class="mb-3">
                        <label for="modal_np_origem" class="form-label">Origem</label>
                        <select id="modal_np_origem" class="form-select">
                            <option value="balcao">Balcão</option>
                            <option value="mesa">Mesa</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modal_np_mesa" class="form-label">Mesa / ID</label>
                        <input type="text" id="modal_np_mesa" class="form-control" placeholder="Ex.: 12 ou Balcão 1" autocomplete="off">
                    </div>
                    <div class="mb-0">
                        <label for="modal_np_cliente" class="form-label">Cliente (nome, opcional)</label>
                        <input type="text" id="modal_np_cliente" class="form-control" placeholder="Nome rápido do cliente">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning fw-semibold" id="btn_confirmar_novo_pedido">Criar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo htmlspecialchars(url_da_aplicacao('recursos/js/scripts_painel.js')); ?>"></script>
    <script>
        window.__APP = { urlLogin: <?php echo json_encode(url_da_aplicacao('index.php'), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?> };
    </script>
    <script src="<?php echo htmlspecialchars(url_da_aplicacao('recursos/js/cliente_http.js')); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
        const URL_API_PEDIDOS = <?php echo json_encode(url_da_aplicacao('apis/pedidos.php'), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?>;
        const URL_API_ITENS = <?php echo json_encode(url_da_aplicacao('apis/itens_do_cardapio.php'), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?>;

        const elModalNovoPedido = document.getElementById('modal_novo_pedido');
        if (elModalNovoPedido) {
            elModalNovoPedido.addEventListener('shown.bs.modal', function () {
                const campo = document.getElementById('modal_np_mesa');
                if (campo) {
                    campo.focus();
                    campo.select();
                }
            });
        }

        // Garantia extra: remove backdrops órfãos ao fechar qualquer modal
        document.addEventListener('hidden.bs.modal', function () {
            if (!document.querySelector('.modal.show')) {
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }
        });

        function labelOrigem(origem) {
            if (origem === 'delivery') return '<i class="fa-solid fa-motorcycle"></i> Entrega';
            if (origem === 'mesa') return '<i class="fa-solid fa-chair"></i> Mesa';
            return '<i class="fa-solid fa-store"></i> Balcão';
        }

        function classeTipo(origem) {
            if (origem === 'delivery') return 'tipo_delivery';
            if (origem === 'mesa') return 'tipo_mesa';
            return 'tipo_mesa';
        }

        function configurarFiltros() {
            const filtros = document.querySelectorAll('.filtro_header');
            filtros.forEach(filtro => {
                filtro.onclick = () => {
                    filtros.forEach(f => f.classList.remove('active'));
                    filtro.classList.add('active');
                    const categoria = filtro.getAttribute('data-filter');
                    document.querySelectorAll('.card_pedido').forEach(card => {
                        const tipoCard = card.getAttribute('data-tipo');
                        const ok = categoria === 'todos' || tipoCard === categoria;
                        card.style.display = ok ? 'block' : 'none';
                    });
                };
            });
        }

        function iniciarCronometros() {
            document.querySelectorAll('.tempo_regressivo').forEach(display => {
                if (display.getAttribute('data-running')) return;
                display.setAttribute('data-running', 'true');
                let tempoRestante = parseInt(display.getAttribute('data-tempo'), 10);
                const intervalo = setInterval(() => {
                    if (tempoRestante <= 0) {
                        clearInterval(intervalo);
                        const container = display.closest('.cronometro_container');
                        if (container) {
                            container.style.backgroundColor = '#fee2e2';
                            container.style.color = '#ef4444';
                        }
                        display.innerHTML = 'ATRASADO';
                        return;
                    }
                    tempoRestante--;
                    const minutos = Math.floor(tempoRestante / 60);
                    const segundos = tempoRestante % 60;
                    display.innerHTML = (minutos < 10 ? '0' + minutos : minutos) + ':' + (segundos < 10 ? '0' + segundos : segundos);
                    display.setAttribute('data-tempo', tempoRestante);
                }, 1000);
            });
        }

        function togglePedido(botao) {
            const card = botao.closest('.card_pedido');
            card.classList.toggle('ativo');
            const icone = botao.querySelector('i');
            if (card.classList.contains('ativo')) {
                icone.classList.replace('fa-chevron-down', 'fa-chevron-up');
            } else {
                icone.classList.replace('fa-chevron-up', 'fa-chevron-down');
            }
        }

        async function acaoPedido(acao, corpo) {
            const data = await apiFetch(URL_API_PEDIDOS + '?acao=' + encodeURIComponent(acao), {
                method: 'POST',
                body: JSON.stringify(corpo)
            });
            if (!data.success) {
                alert(data.message || 'Erro na operação.');
                return false;
            }
            await carregarPedidos();
            return true;
        }

        async function carregarPedidos() {
            const container = document.getElementById('container_cards_pedidos');
            const data = await apiFetch(URL_API_PEDIDOS + '?acao=listar&somente_ativos=1', { method: 'GET' });

            const lista = (data.success && data.dados && Array.isArray(data.dados.pedidos)) ? data.dados.pedidos : [];

            if (data.success && lista.length > 0) {
                container.innerHTML = '';

                lista.forEach(pedido => {
                    let itensHTML = '';
                    (pedido.itens || []).forEach(item => {
                        let adic = '';
                        (item.adicionais || []).forEach(a => {
                            adic += `<small style="display:block;color:var(--cinza-texto);">+ ${a.quantidade}x ${a.nome}</small>`;
                        });
                        itensHTML += `<p><strong>${item.quantidade}x</strong> ${item.nome}</p>${adic}`;
                    });

                    const st = pedido.status || 'pendente';
                    const bloqueado = (st === 'concluido' || st === 'cancelado');

                    const card = document.createElement('div');
                    card.className = 'card_pedido ' + classeTipo(pedido.origem);
                    card.setAttribute('data-tipo', pedido.origem);
                    card.innerHTML = `
                        <div class="cabecalho_card">
                            <span class="id_pedido">#${pedido.id_pedido}</span>
                            <span class="status_badge badge_${st.replace(/[^a-z0-9]/gi, '_')}" style="font-size:11px;">${st}</span>
                            <div class="cronometro_container">
                                <i class="fa-regular fa-clock"></i>
                                <span class="tempo_regressivo" data-tempo="1800">30:00</span>
                            </div>
                        </div>
                        <div class="info_cliente">
                            <h4>${pedido.cliente || 'Mesa ' + (pedido.mesa || 'S/N')}</h4>
                            <p>${labelOrigem(pedido.origem)}</p>
                        </div>
                        <div class="valor_total_exibido">
                            <span>Total: <strong>R$ ${pedido.valor_total}</strong></span>
                        </div>
                        <div class="acoes_pedido_botoes">
                            <button type="button" class="btn_concluir" data-pid="${pedido.id_pedido}" ${bloqueado ? 'disabled' : ''}><i class="fa-solid fa-check"></i></button>
                            <button type="button" class="btn_imprimir" data-pid="${pedido.id_pedido}"><i class="fa-solid fa-print"></i></button>
                            <button type="button" class="btn_rejeitar" data-pid="${pedido.id_pedido}" ${bloqueado ? 'disabled' : ''}><i class="fa-solid fa-xmark"></i></button>
                        </div>
                        <div class="detalhes_ocultos">
                            <hr class="divisor_card">
                            <div class="lista_itens_do_pedido">${itensHTML || '<p>Sem itens.</p>'}</div>
                            <div class="endereco_detalhe">
                                <p><strong>Endereço:</strong> ${pedido.rua ? pedido.rua + ', ' + (pedido.numero || '') : 'Balcão/Mesa'}</p>
                                <p><strong>Bairro:</strong> ${pedido.bairro || '-'}</p>
                            </div>
                            <div style="margin-top:12px;display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                                <select class="sel_item_cardapio" data-pid="${pedido.id_pedido}" style="flex:1;min-width:160px;padding:8px;border-radius:8px;border:1px solid var(--cor-bordas);">
                                    <option value="">Item do cardápio…</option>
                                </select>
                                <input type="number" class="inp_qtd_item" data-pid="${pedido.id_pedido}" value="1" min="1" style="width:70px;padding:8px;border-radius:8px;border:1px solid var(--cor-bordas);">
                                <button type="button" class="btn_login btn_add_item" data-pid="${pedido.id_pedido}" style="width:auto;padding:8px 12px;font-size:14px;" ${bloqueado ? 'disabled' : ''}>Adicionar item</button>
                            </div>
                        </div>
                        <button class="btn_expandir" type="button"><i class="fa-solid fa-chevron-down"></i> Ver itens e ações</button>
                    `;

                    card.querySelector('.btn_expandir').addEventListener('click', function () { togglePedido(this); });

                    card.querySelector('.btn_concluir').addEventListener('click', async () => {
                        if (!confirm('Concluir este pedido e registrar entrada no caixa?')) return;
                        await acaoPedido('concluir', { id_pedido: parseInt(pedido.id_pedido, 10) });
                    });
                    card.querySelector('.btn_rejeitar').addEventListener('click', async () => {
                        if (!confirm('Cancelar este pedido?')) return;
                        await acaoPedido('cancelar', { id_pedido: parseInt(pedido.id_pedido, 10) });
                    });
                    card.querySelector('.btn_imprimir').addEventListener('click', () => {
                        const printArea = document.getElementById('print_section');
                        let ticketItens = '';
                        (pedido.itens || []).forEach(item => {
                            let adics = '';
                            (item.adicionais || []).forEach(a => {
                                adics += `<div style="margin-left: 15px; font-size: 13px;">+ ${a.quantidade}x ${a.nome}</div>`;
                            });
                            ticketItens += `<div style="margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                                <div style="display: flex; justify-content: space-between;">
                                    <strong>${item.quantidade}x ${item.nome}</strong>
                                </div>
                                ${adics}
                            </div>`;
                        });

                        printArea.innerHTML = `
                            <div style="max-width: 300px; margin: 0 auto; border: 1px solid #000; padding: 15px; background: #fff;">
                                <div style="text-align: center; border-bottom: 2px solid #000; margin-bottom: 10px; padding-bottom: 5px;">
                                    <h2 style="margin: 0; font-size: 20px;">TICKET DE COZINHA</h2>
                                    <span style="font-size: 12px;">${new Date().toLocaleString('pt-BR')}</span>
                                </div>
                                <div style="margin-bottom: 10px; font-size: 15px;">
                                    <div><strong>PEDIDO:</strong> #${pedido.id_pedido}</div>
                                    <div><strong>CLIENTE:</strong> ${pedido.cliente || (pedido.mesa ? 'Mesa ' + pedido.mesa : 'Não informado')}</div>
                                    <div><strong>ORIGEM:</strong> ${pedido.origem.toUpperCase()}</div>
                                </div>
                                <div style="border-top: 2px solid #000; padding-top: 10px;">
                                    ${ticketItens || '<div style="text-align: center;">Sem itens no pedido.</div>'}
                                </div>
                                <div style="margin-top: 15px; border-top: 1px dashed #000; padding-top: 10px; text-align: right;">
                                    <strong>VALOR TOTAL: R$ ${pedido.valor_total}</strong>
                                </div>
                                <div style="text-align: center; margin-top: 20px; font-size: 11px; border-top: 1px solid #ccc; padding-top: 10px;">
                                    Sistema de Gestão de Cardápio
                                </div>
                            </div>
                        `;
                        window.print();
                    });

                    const sel = card.querySelector('.sel_item_cardapio');
                    const carregarOpts = async () => {
                        const r = await apiFetch(URL_API_ITENS + '?acao=listar_itens', { method: 'GET' });
                        if (!r.success || !r.dados || !r.dados.itens) return;
                        sel.innerHTML = '<option value="">Item do cardápio…</option>' +
                            r.dados.itens.map(it => `<option value="${it.id_item}">${it.nome} (R$ ${parseFloat(it.preco).toFixed(2).replace('.', ',')})</option>`).join('');
                    };
                    carregarOpts();

                    card.querySelector('.btn_add_item').addEventListener('click', async () => {
                        const pid = parseInt(card.querySelector('.btn_add_item').getAttribute('data-pid'), 10);
                        const itemId = parseInt(sel.value, 10);
                        const qtd = parseInt(card.querySelector('.inp_qtd_item').value, 10) || 1;
                        if (!itemId) {
                            alert('Selecione um item do cardápio.');
                            return;
                        }
                        await acaoPedido('adicionar_item', { id_pedido: pid, id_item: itemId, quantidade: qtd, adicionais: [] });
                    });

                    container.appendChild(card);
                });

                iniciarCronometros();
            } else {
                const msg = data.success ? 'Nenhum pedido ativo no momento.' : (data.message || 'Não foi possível carregar os pedidos.');
                container.innerHTML = '<p class="text-center text-secondary py-4">' + msg + '</p>';
            }
        }

        document.getElementById('btn_confirmar_novo_pedido').addEventListener('click', async () => {
            const origem = document.getElementById('modal_np_origem').value;
            const identificador_mesa = document.getElementById('modal_np_mesa').value.trim() || null;
            const nome = document.getElementById('modal_np_cliente').value.trim();
            const body = { origem, identificador_mesa, taxa_entrega: 0, desconto: 0 };
            if (nome) {
                body.cliente_rapido = { nome, telefone: null, rua: null, numero: null, bairro: null };
            }
            const data = await apiFetch(URL_API_PEDIDOS + '?acao=criar', { method: 'POST', body: JSON.stringify(body) });
            if (!data.success) {
                alert(data.message || 'Erro ao criar pedido.');
                return;
            }
            const modalEl = document.getElementById('modal_novo_pedido');
            const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modalInstance.hide();
            
            // Força a remoção do backdrop caso o Bootstrap falhe (bug relatado)
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }

            document.getElementById('modal_np_mesa').value = '';
            document.getElementById('modal_np_cliente').value = '';
            document.getElementById('modal_np_origem').value = 'balcao';
            await carregarPedidos();
        });

        document.addEventListener('DOMContentLoaded', () => {
            carregarPedidos();
            configurarFiltros();
        });
    </script>
</body>
</html>
