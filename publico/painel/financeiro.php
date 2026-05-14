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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Financeiro — Sistema de Gestão</title>
    <style>
        #main_financeiro { flex: 1; padding: 2rem; background-color: #f8f9fc; }
        #header_financeiro { margin-bottom: 2.5rem; }
        #header_financeiro h2 { font-weight: 700; color: #333; }
        
        .organiza_financeiro { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 1.5rem; 
            margin-bottom: 2.5rem; 
        }
        
        .card_resumo { 
            background: #fff; 
            border-radius: 12px; 
            padding: 1.5rem; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
            transition: transform 0.2s ease;
            border: none !important;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card_resumo:hover { transform: translateY(-5px); }
        
        .dashboard-card-vendas { border-top: 4px solid #16a34a !important; }
        .dashboard-card-lucro { border-top: 4px solid #f39c12 !important; }
        .dashboard-card-despesas { border-top: 4px solid #dc2626 !important; }
        
        .topo_resumo { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
        .icone_bg { width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 1.25rem; }
        .verde_claro { background: #e8f5e9; color: #16a34a; }
        .laranja_claro { background: #fff3e0; color: #f39c12; }
        .vermelho_claro { background: #ffebee; color: #dc2626; }
        
        .titulo_card { font-weight: 600; color: #666; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .conteudo_valor h1 { font-size: 1.8rem; font-weight: 800; margin-bottom: 0.25rem; }
        .conteudo_valor p { font-size: 0.8rem; color: #999; margin: 0; }

        .container_grafico_mensal { 
            background: #fff; 
            padding: 1.5rem; 
            border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
            margin-bottom: 2rem; 
        }
        .header_grafico { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .header_grafico h3 { font-size: 1.1rem; font-weight: 700; color: #333; margin: 0; }

        .btn_ocultar_laranja, .btn_adicionar_laranja {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            border: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn_ocultar_laranja { background: #eee; color: #555; }
        .btn_adicionar_laranja { background: var(--cor-principal); color: #fff; }
        .btn_ocultar_laranja:hover { background: #e0e0e0; }
        .btn_adicionar_laranja:hover { background: var(--cor-principal-destaque); transform: scale(1.02); }

        .container_lista_sanfona { background: #fff; border-radius: 12px; overflow: hidden; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.03); border: 1px solid #edf2f7; }
        .container_lista_sanfona summary { padding: 1.25rem; background: #fff; list-style: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #edf2f7; }
        .container_lista_sanfona summary::-webkit-details-marker { display: none; }
        
        .summary_left { display: flex; align-items: center; gap: 1rem; font-weight: 700; color: #333; }
        .badge_notificacao { background: #edf2f7; color: #4a5568; padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.75rem; }
        
        .item_registro_financeiro { 
            padding: 1rem 1.25rem; 
            border-bottom: 1px solid #f7fafc; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            transition: background 0.2s;
        }
        .item_registro_financeiro:hover { background: #f8fafc; }
        .item_registro_financeiro h3 { font-size: 0.95rem; font-weight: 600; color: #2d3748; margin: 0 0 0.25rem 0; }
        .item_registro_financeiro p { font-size: 0.8rem; color: #718096; margin: 0; }
        .registro_valor { font-weight: 700; font-size: 1rem; }
    </style>
</head>
<body>
    <?php include dirname(__DIR__, 2) . '/componentes/nav.php'; ?>

    <main id="main_financeiro" class="px-2 px-md-3">

        <header id="header_financeiro" class="pt-3 pb-2">
            <h2>Financeiro</h2>
            <p id="financeiro_subtitulo" style="font-size:13px;color:var(--cinza-texto);margin-top:4px;">Carregando dados…</p>
        </header>

        <section class="organiza_financeiro" aria-label="Resumo do mês">
            <div class="card_resumo dashboard-card-vendas">
                <div class="topo_resumo">
                    <span class="icone_bg verde_claro"><i class="fa-solid fa-cart-shopping" aria-hidden="true"></i></span>
                    <span class="titulo_card">Total de vendas</span>
                </div>
                <div class="conteudo_valor">
                    <h1 class="valor_sensivel valor_sem_fundo text-success" id="card_total_vendas" data-valor="R$ 0,00">R$ 0,00</h1>
                    <p>Faturamento de pedidos no mês atual</p>
                </div>
            </div>

            <div class="card_resumo dashboard-card-lucro">
                <div class="topo_resumo">
                    <span class="icone_bg laranja_claro"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></span>
                    <span class="titulo_card">Lucro líquido</span>
                </div>
                <div class="conteudo_valor">
                    <h1 class="valor_sensivel valor_sem_fundo text-success" id="card_lucro_liquido" data-valor="R$ 0,00">R$ 0,00</h1>
                    <p>Entradas totais − despesas − saídas de caixa (mês)</p>
                </div>
            </div>

            <div class="card_resumo dashboard-card-despesas">
                <div class="topo_resumo">
                    <span class="icone_bg vermelho_claro"><i class="fa-solid fa-file-invoice-dollar" aria-hidden="true"></i></span>
                    <span class="titulo_card">Despesas</span>
                </div>
                <div class="conteudo_valor">
                    <h1 class="valor_sensivel valor_sem_fundo text-danger" id="card_despesas_mes" data-valor="R$ 0,00">R$ 0,00</h1>
                    <p>Compromissos registrados no mês atual</p>
                </div>
            </div>
        </section>

        <div style="margin-bottom: 25px;">
            <button type="button" onclick="toggleGlobalValores()" class="btn_ocultar_laranja">
                <i id="icone_olho" class="fas fa-eye-slash"></i> Ocultar valores
            </button>
            <button type="button" onclick="carregarTudo()" class="btn_ocultar_laranja" style="margin-left:8px;">
                <i class="fa-solid fa-rotate"></i> Atualizar
            </button>
        </div>

        <div class="container_grafico_mensal mb-4">
            <div class="header_grafico">
                <h3>Vendas nos últimos 7 dias</h3>
            </div>
            <p id="grafico_semana_loading" style="font-size:13px;color:var(--cinza-texto);margin-bottom:8px;"></p>
            <div style="position:relative;height:min(280px,50vw);max-height:320px;width:100%;">
                <canvas id="graficoSemana" aria-label="Gráfico de vendas dos últimos sete dias"></canvas>
            </div>
        </div>

        <div class="container_grafico_mensal">
            <div class="header_grafico">
                <h3>Gráfico mensal</h3>
                <select id="select_ano_grafico" aria-label="Ano do gráfico"></select>
            </div>
            <p id="grafico_loading" style="font-size:13px;color:var(--cinza-texto);margin-bottom:8px;"></p>
            <div style="position:relative;height:min(320px,55vw);max-height:380px;width:100%;">
                <canvas id="graficoMensal"></canvas>
            </div>
        </div>

        <details class="container_lista_sanfona" open>
            <summary>
                <div class="summary_left">
                    <i class="fa-solid fa-sack-dollar laranja"></i>
                    <span>Despesas do mês</span>
                    <span class="badge_notificacao" id="badge_despesas">0</span>
                </div>
                <i class="fa-solid fa-chevron-up seta_sanfona"></i>
            </summary>

            <div class="conteudo_sanfona">
                <div class="sanfona_header_acoes">
                    <span class="texto_total">Total no mês: <strong id="total_despesas_mes">R$ 0,00</strong></span>
                    <button type="button" class="btn_adicionar_laranja" onclick="abrirModal('modal_despesa')" title="Adicionar Despesa">
                        <i class="fa-solid fa-plus"></i> <span>Adicionar</span>
                    </button>
                </div>
                <div id="lista_despesas_container"></div>
            </div>
        </details>

        <details class="container_lista_sanfona" open>
            <summary>
                <div class="summary_left">
                    <i class="fa-solid fa-coins azul"></i>
                    <span>Controle de caixa</span>
                    <span class="badge_notificacao azul_bg" id="badge_movimentos">0</span>
                </div>
                <i class="fa-solid fa-chevron-up seta_sanfona"></i>
            </summary>

            <div class="conteudo_sanfona">
                <div class="sanfona_header_acoes" style="flex-wrap:wrap;gap:10px;">
                    <div>
                        <strong id="txt_status_caixa">Sessão: —</strong>
                        <p style="margin:4px 0 0;font-size:13px;color:var(--cinza-texto);">
                            Saldo estimado (sessão): <span id="txt_saldo_sessao">—</span> ·
                            Saldo global (movimentações): <span id="txt_saldo_global">—</span>
                        </p>
                    </div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <button type="button" class="btn_adicionar_laranja" onclick="abrirCaixa()"><span>Abrir caixa</span></button>
                        <button type="button" class="btn_ocultar_laranja" onclick="fecharCaixa()"><span>Fechar caixa</span></button>
                        <button type="button" class="btn_adicionar_laranja" onclick="abrirModal('modal_movimentacao')" title="Adicionar Movimentação">
                        <i class="fa-solid fa-plus"></i> <span>Adicionar</span>
                    </button>
                    </div>
                </div>
                <div id="lista_movimentacoes_container"></div>
            </div>
        </details>

    </main>

    <div id="modal_movimento" class="modal_overlay_financeiro">
        <div class="modal_card_financeiro">
            <div class="modal_topo">
                <h3>Lançamento no caixa</h3>
                <i class="fa-solid fa-xmark btn_fechar_modal" onclick="fecharModal('modal_movimento')"></i>
            </div>
            <div class="modal_corpo">
                <div class="campo_modal_grupo">
                    <label>Tipo</label>
                    <select id="mov_tipo">
                        <option value="entrada">Entrada (suprimento / outros)</option>
                        <option value="saida">Saída (sangria / retirada)</option>
                    </select>
                </div>
                <div class="campo_modal_grupo">
                    <label>Valor</label>
                    <input type="number" id="mov_valor" min="0" step="0.01" placeholder="0.00">
                </div>
                <div class="campo_modal_grupo">
                    <label>Descrição</label>
                    <input type="text" id="mov_desc" placeholder="Ex: Troco, suprimento">
                </div>
                <button type="button" class="btn_finalizar" onclick="salvarMovimento()">Registrar</button>
            </div>
        </div>
    </div>

    <div id="modal_despesa" class="modal_overlay_financeiro">
        <div class="modal_card_financeiro">
            <div class="modal_topo">
                <h3>Registrar despesa</h3>
                <i class="fa-solid fa-xmark btn_fechar_modal" onclick="fecharModal('modal_despesa')"></i>
            </div>
            <div class="modal_corpo">
                <div class="campo_modal_grupo">
                    <label>Fornecedor</label>
                    <input type="text" id="despesa_fornecedor" placeholder="Ex: Fornecedor de carnes">
                </div>
                <div class="campo_modal_grupo">
                    <label>Data</label>
                    <input type="date" id="despesa_data" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="campo_modal_grupo">
                    <label>Valor</label>
                    <input type="number" id="despesa_valor" placeholder="0.00" min="0" step="0.01">
                </div>
                <div class="campo_modal_grupo">
                    <label>Tipo</label>
                    <select id="despesa_tipo">
                        <option value="fixa">Fixa</option>
                        <option value="variavel">Variável</option>
                    </select>
                </div>
                <div class="campo_modal_grupo">
                    <label>Observação</label>
                    <textarea id="despesa_obs" rows="3" placeholder="Ex: Pagamento do mês"></textarea>
                </div>
                <button type="button" class="btn_finalizar" onclick="salvarDespesa()">Salvar despesa</button>
            </div>
        </div>
    </div>

    <script src="<?php echo htmlspecialchars(url_da_aplicacao('recursos/js/scripts_painel.js')); ?>"></script>
    <script>
        window.__APP = { urlLogin: <?php echo json_encode(url_da_aplicacao('index.php'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?> };
    </script>
    <script src="<?php echo htmlspecialchars(url_da_aplicacao('recursos/js/cliente_http.js')); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
        const URL_API_FIN = <?php echo json_encode(url_da_aplicacao('apis/financeiro.php'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?>;
        const URL_API_REL = <?php echo json_encode(url_da_aplicacao('apis/relatorios.php'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?>;
        const URL_API_MOV = <?php echo json_encode(url_da_aplicacao('apis/movimentacoes.php'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?>;
        const URL_API_CX = <?php echo json_encode(url_da_aplicacao('apis/caixa.php'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?>;

        let valoresOcultos = false;
        let graficoMensal = null;
        let graficoSemanal = null;

        const NOMES_MESES = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];

        function brl(n) {
            const v = Number(n) || 0;
            return 'R$ ' + v.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function setCardValor(elId, valorNumero) {
            const el = document.getElementById(elId);
            if (!el) return;
            const txt = brl(valorNumero);
            el.setAttribute('data-valor', txt);
            if (!valoresOcultos) el.textContent = txt;
            else el.textContent = 'R$ ••••••';
        }

        function toggleGlobalValores() {
            const h1s = document.querySelectorAll('.valor_sensivel');
            const icone = document.getElementById('icone_olho');
            valoresOcultos = !valoresOcultos;
            h1s.forEach(h1 => {
                if (valoresOcultos) {
                    h1.innerText = 'R$ ••••••';
                    icone.className = 'fas fa-eye';
                } else {
                    h1.innerText = h1.getAttribute('data-valor') || 'R$ 0,00';
                    icone.className = 'fas fa-eye-slash';
                }
            });
        }

        function abrirModal(id) {
            const modal = document.getElementById(id);
            if (modal) modal.style.display = 'flex';
        }

        function fecharModal(id) {
            const modal = document.getElementById(id);
            if (modal) modal.style.display = 'none';
        }

        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal_overlay_financeiro')) {
                fecharModal(event.target.id);
            }
        });

        window.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                fecharModal('modal_movimento');
                fecharModal('modal_despesa');
            }
        });

        async function carregarCardsDashboard() {
            const ano = new Date().getFullYear();
            const mes = new Date().getMonth() + 1;
            const r = await apiFetch(URL_API_REL + '?acao=resumo_mes&ano=' + ano + '&mes=' + mes);
            const sub = document.getElementById('financeiro_subtitulo');
            if (!r.success) {
                if (sub) sub.textContent = r.message ? r.message : 'Erro ao carregar resumo do mês.';
                return;
            }
            const d = r.dados || {};
            setCardValor('card_total_vendas', d.faturamento);
            setCardValor('card_lucro_liquido', d.lucro);
            setCardValor('card_despesas_mes', d.despesas);
            if (sub) {
                sub.textContent = 'Resumo de ' + NOMES_MESES[mes - 1] + ' de ' + ano +
                    ' · atualizado em ' + new Date().toLocaleString('pt-BR');
            }
        }

        async function carregarGraficoSemana() {
            const load = document.getElementById('grafico_semana_loading');
            if (load) load.textContent = 'Carregando vendas da semana…';
            const r = await apiFetch(URL_API_REL + '?acao=vendas_ultimos_7_dias');
            if (load) load.textContent = '';
            if (!r.success || !r.dados || !Array.isArray(r.dados.dias)) {
                if (load) load.textContent = (r && r.message) ? r.message : 'Erro ao carregar vendas dos últimos 7 dias.';
                return;
            }
            const dias = r.dados.dias;
            const labels = dias.map(x => x.label);
            const valores = dias.map(x => x.vendas);
            const canvas = document.getElementById('graficoSemana');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            if (graficoSemanal) {
                graficoSemanal.data.labels = labels;
                graficoSemanal.data.datasets[0].data = valores;
                graficoSemanal.update();
            } else {
                graficoSemanal = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Vendas (R$)',
                            data: valores,
                            backgroundColor: 'rgba(22, 163, 74, 0.78)',
                            borderColor: 'rgba(21, 128, 61, 1)',
                            borderWidth: 1,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label(ctx) {
                                        const v = ctx.raw;
                                        return 'Vendas: R$ ' + Number(v).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback(value) {
                                        return 'R$ ' + Number(value).toLocaleString('pt-BR');
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        async function carregarDespesasLista() {
            const r = await apiFetch(URL_API_FIN + '?acao=listar_despesas');
            const box = document.getElementById('lista_despesas_container');
            const badge = document.getElementById('badge_despesas');
            const totalMesEl = document.getElementById('total_despesas_mes');
            if (!r.success) {
                box.innerHTML = '<p class="txt_vazio">Não foi possível carregar despesas.</p>';
                return;
            }
            const ano = new Date().getFullYear();
            const mes = String(new Date().getMonth() + 1).padStart(2, '0');
            const lista = (r.dados && r.dados.despesas) ? r.dados.despesas : [];
            let totalMes = 0;
            const noMes = lista.filter(d => {
                if (!d.data_lancamento) return false;
                const ok = d.data_lancamento.startsWith(ano + '-' + mes);
                if (ok) totalMes += Number(d.valor) || 0;
                return ok;
            });
            badge.textContent = String(noMes.length);
            totalMesEl.textContent = brl(totalMes);
            if (noMes.length === 0) {
                box.innerHTML = '<p class="txt_vazio">Nenhuma despesa neste mês.</p>';
                return;
            }
            box.innerHTML = noMes.map(d => {
                const tipo = (d.tipo === 'fixa') ? 'Fixa' : 'Variável';
                const dataBr = d.data_lancamento ? new Date(d.data_lancamento + 'T12:00:00').toLocaleDateString('pt-BR') : '';
                return `
                <div class="item_registro_financeiro" data-id="${d.id_despesa}">
                    <div class="registro_info">
                        <h3>${escapeHtml(d.fornecedor)}</h3>
                        <p><i class="fa-regular fa-calendar"></i> ${dataBr}</p>
                        <div class="registro_status">
                            <span class="tag_status_fixa">${tipo}</span>
                            <span>${escapeHtml((d.observacao || '').slice(0, 80))}</span>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div class="registro_valor">${brl(d.valor)}</div>
                        <button type="button" class="btn_acao_vermelho" title="Desativar" onclick="desativarDespesa(${d.id_despesa})" style="background:none; border:none; color:#dc2626; padding:8px; cursor:pointer;"><i class="fa-solid fa-trash-can"></i></button>
                    </div>
                </div>`;
            }).join('');
        }

        function escapeHtml(s) {
            const t = document.createElement('div');
            t.textContent = s;
            return t.innerHTML;
        }

        async function desativarDespesa(id) {
            if (!confirm('Desativar esta despesa?')) return;
            const r = await apiFetch(URL_API_FIN + '?acao=desativar_despesa', {
                method: 'POST',
                body: JSON.stringify({ id_despesa: id })
            });
            if (r && r.success) {
                await carregarTudo();
            } else {
                alert((r && r.message) ? r.message : 'Erro');
            }
        }

        async function salvarDespesa() {
            const fornecedor = document.getElementById('despesa_fornecedor').value.trim();
            const valor = parseFloat(document.getElementById('despesa_valor').value);
            const data = document.getElementById('despesa_data').value;
            const tipo = document.getElementById('despesa_tipo').value;
            const obs = document.getElementById('despesa_obs').value.trim();
            if (!fornecedor || !valor || valor <= 0) {
                alert('Preencha fornecedor e valor.');
                return;
            }
            const r = await apiFetch(URL_API_FIN + '?acao=criar_despesa', {
                method: 'POST',
                body: JSON.stringify({
                    fornecedor,
                    valor,
                    data_lancamento: data,
                    tipo,
                    observacao: obs || null
                })
            });
            if (r && r.success) {
                fecharModal('modal_despesa');
                document.getElementById('despesa_fornecedor').value = '';
                document.getElementById('despesa_valor').value = '';
                document.getElementById('despesa_obs').value = '';
                await carregarTudo();
            } else {
                alert((r && r.message) ? r.message : 'Erro ao salvar.');
            }
        }

        async function carregarMovimentacoes() {
            const r = await apiFetch(URL_API_MOV + '?acao=listar&limite=80');
            const box = document.getElementById('lista_movimentacoes_container');
            const badge = document.getElementById('badge_movimentos');
            if (!r.success) {
                box.innerHTML = '<p class="txt_vazio">Não foi possível carregar movimentações.</p>';
                return;
            }
            const lista = (r.dados && r.dados.movimentacoes) ? r.dados.movimentacoes : [];
            badge.textContent = String(Math.min(lista.length, 99));
            if (lista.length === 0) {
                box.innerHTML = '<p class="txt_vazio">Nenhuma movimentação registrada.</p>';
                return;
            }
            box.innerHTML = lista.map(m => {
                const dt = m.criado_em ? new Date(m.criado_em.replace(' ', 'T')).toLocaleString('pt-BR') : '';
                const cls = m.tipo === 'entrada' ? 'verde' : 'vermelho';
                const sign = m.tipo === 'entrada' ? '+' : '−';
                return `
                <div class="item_registro_financeiro">
                    <div class="registro_info">
                        <h3>${escapeHtml(m.descricao || m.origem || 'Movimentação')}</h3>
                        <p><i class="fa-regular fa-clock"></i> ${dt} · <span class="tag_status_fixa">${escapeHtml(m.tipo)}</span> · ${escapeHtml(m.origem || '')}</p>
                    </div>
                    <div class="registro_valor ${cls}">${sign} ${brl(m.valor)}</div>
                </div>`;
            }).join('');
        }

        async function carregarStatusCaixa() {
            const r = await apiFetch(URL_API_CX + '?acao=status');
            const st = document.getElementById('txt_status_caixa');
            const ss = document.getElementById('txt_saldo_sessao');
            const sg = document.getElementById('txt_saldo_global');
            if (!r.success || !r.dados) {
                st.textContent = 'Sessão: (indisponível)';
                return;
            }
            const s = r.dados.sessao_aberta;
            if (s) {
                st.textContent = 'Sessão aberta desde ' + new Date(s.aberto_em.replace(' ', 'T')).toLocaleString('pt-BR');
            } else {
                st.textContent = 'Nenhuma sessão aberta';
            }
            ss.textContent = (r.dados.saldo_estimado_sessao != null) ? brl(r.dados.saldo_estimado_sessao) : '—';
            sg.textContent = (r.dados.saldo_movimentacoes_global != null) ? brl(r.dados.saldo_movimentacoes_global) : '—';
        }

        async function abrirCaixa() {
            const obs = prompt('Observação da abertura (opcional):', '') || '';
            const saldoTxt = prompt('Saldo informado na abertura (opcional, use 0 se não souber):', '0');
            const saldo = parseFloat(String(saldoTxt).replace(',', '.')) || 0;
            const r = await apiFetch(URL_API_CX + '?acao=abrir', {
                method: 'POST',
                body: JSON.stringify({ saldo_informado_abertura: saldo, observacao: obs || null })
            });
            if (r && r.success) {
                await carregarTudo();
            } else {
                alert((r && r.message) ? r.message : 'Erro');
            }
        }

        async function fecharCaixa() {
            const obs = prompt('Observação do fechamento (opcional):', '') || '';
            const r = await apiFetch(URL_API_CX + '?acao=fechar', {
                method: 'POST',
                body: JSON.stringify({ observacao: obs || null })
            });
            if (r && r.success) {
                alert('Caixa fechado. Saldo calculado: ' + brl(r.dados.saldo_calculado));
                await carregarTudo();
            } else {
                alert((r && r.message) ? r.message : 'Erro');
            }
        }

        async function salvarMovimento() {
            const tipo = document.getElementById('mov_tipo').value;
            const valor = parseFloat(document.getElementById('mov_valor').value);
            const desc = document.getElementById('mov_desc').value.trim();
            if (!valor || valor <= 0) {
                alert('Informe um valor válido.');
                return;
            }
            const acao = tipo === 'entrada' ? 'registrar_entrada' : 'registrar_saida';
            const r = await apiFetch(URL_API_MOV + '?acao=' + acao, {
                method: 'POST',
                body: JSON.stringify({
                    valor,
                    descricao: desc || (tipo === 'entrada' ? 'Entrada manual' : 'Saída manual'),
                    origem: tipo === 'entrada' ? 'manual_entrada' : 'sangria'
                })
            });
            if (r && r.success) {
                fecharModal('modal_movimento');
                document.getElementById('mov_valor').value = '';
                document.getElementById('mov_desc').value = '';
                await carregarTudo();
            } else {
                alert((r && r.message) ? r.message : 'Erro');
            }
        }

        function preencherAnosGrafico() {
            const sel = document.getElementById('select_ano_grafico');
            const y = new Date().getFullYear();
            sel.innerHTML = '';
            for (let a = y; a >= y - 5; a--) {
                const o = document.createElement('option');
                o.value = String(a);
                o.textContent = String(a);
                if (a === y) o.selected = true;
                sel.appendChild(o);
            }
        }

        async function atualizarGrafico() {
            const ano = parseInt(document.getElementById('select_ano_grafico').value, 10) || new Date().getFullYear();
            const load = document.getElementById('grafico_loading');
            load.textContent = 'Carregando série ' + ano + '…';
            const r = await apiFetch(URL_API_REL + '?acao=grafico_mensal&ano=' + ano);
            load.textContent = '';
            if (!r.success || !r.dados.meses) {
                load.textContent = (r && r.message) ? r.message : 'Erro ao carregar gráfico.';
                return;
            }
            const meses = r.dados.meses;
            const labels = meses.map(m => m.label);
            const fat = meses.map(m => m.faturamento);
            const des = meses.map(m => m.despesas);
            const luc = meses.map(m => m.lucro);
            const ctx = document.getElementById('graficoMensal').getContext('2d');
            if (graficoMensal) {
                graficoMensal.data.labels = labels;
                graficoMensal.data.datasets[0].data = fat;
                graficoMensal.data.datasets[1].data = des;
                graficoMensal.data.datasets[2].data = luc;
                graficoMensal.update();
            } else {
                graficoMensal = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Faturamento (pedidos)',
                                data: fat,
                                backgroundColor: 'rgba(34, 197, 94, 0.75)',
                                borderColor: 'rgba(34, 197, 94, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Despesas',
                                data: des,
                                backgroundColor: 'rgba(239, 68, 68, 0.75)',
                                borderColor: 'rgba(239, 68, 68, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Lucro',
                                data: luc,
                                backgroundColor: 'rgba(249, 156, 18, 0.75)',
                                borderColor: 'rgba(249, 156, 18, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'R$ ' + Number(value).toLocaleString('pt-BR');
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        async function carregarTudo() {
            await Promise.all([
                carregarCardsDashboard(),
                carregarGraficoSemana(),
                carregarDespesasLista(),
                carregarMovimentacoes(),
                carregarStatusCaixa(),
                atualizarGrafico()
            ]);
        }

        document.getElementById('select_ano_grafico').addEventListener('change', function() {
            atualizarGrafico();
        });

        preencherAnosGrafico();
        carregarTudo();
    </script>
</body>
</html>
