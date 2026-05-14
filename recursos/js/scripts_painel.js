/**
 * SISTEMA DE GERENCIAMENTO DE CARDÁPIO
 * Código corrigido para compatibilidade com PHP e carregamento dinâmico
 */

document.addEventListener("DOMContentLoaded", () => {
  // 1. MARCAR LINK ATIVO
  // Como agora o PHP faz o include, o JS roda assim que a página abre.
  marcarLinkAtivo();

  // 2. LÓGICA DO MODAL DE FUNCIONÁRIOS
  inicializarModal();

  // 3. LÓGICA DE VISIBILIDADE FINANCEIRA
  // Se o botão de olho existir na página, ele configura o evento
  const btnOlho = document.getElementById("icone_olho_geral");
  if (btnOlho) {
    btnOlho.addEventListener("click", toggleGlobalValores);
  }
});

/**
 * Função para marcar qual página está aberta no menu
 */
function marcarLinkAtivo() {
  // Pega o nome do arquivo atual (ex: dashboard.php)
  const paginaAtual = window.location.pathname.split("/").pop() || "index.php";

  // Busca todos os links dentro da tag <nav>
  const links = document.querySelectorAll("nav a");

  links.forEach((link) => {
    const href = link.getAttribute("href");
    // Verifica se o href do link é igual à página atual
    if (href && (href === paginaAtual || href.includes(paginaAtual))) {
      link.classList.add("nav_link_active");
    }
  });
}

/**
 * Gerencia a abertura e fechamento do modal
 */
function inicializarModal() {
  const botaoNovoFuncionario = document.querySelector(".filtro_funcionarios");
  const modal = document.getElementById("modal_funcionario");
  const fecharModal = document.getElementById("fechar_modal");

  // Só executa se os elementos existirem na página atual
  if (botaoNovoFuncionario && modal) {
    botaoNovoFuncionario.addEventListener("click", (e) => {
      e.preventDefault();
      modal.classList.add("active");
    });

    if (fecharModal) {
      fecharModal.addEventListener("click", () => {
        modal.classList.remove("active");
      });
    }

    // Fecha o modal ao clicar fora dele (na parte escura)
    window.addEventListener("click", (e) => {
      if (e.target === modal) {
        modal.classList.remove("active");
      }
    });
  }
}

/**
 * Esconde ou mostra valores sensíveis no financeiro
 */
function toggleGlobalValores() {
  const valores = document.querySelectorAll(".valor_sensivel");
  const iconeGeral = document.getElementById("icone_olho_geral");

  if (!iconeGeral) return;

  const estaOculto = iconeGeral.classList.contains("fa-eye");

  valores.forEach((v) => {
    if (estaOculto) {
      // MOSTRAR: Pega o valor que deve estar no atributo data-valor
      const valorReal = v.getAttribute("data-valor");
      v.innerText = valorReal ? valorReal : v.innerText;
      v.classList.remove("valor_oculto");
    } else {
      // OCULTAR
      v.innerText = "•••••";
      v.classList.add("valor_oculto");
    }
  });

  // Alterna a classe do ícone (FontAwesome)
  if (estaOculto) {
    iconeGeral.classList.replace("fa-eye", "fa-eye-slash");
  } else {
    iconeGeral.classList.replace("fa-eye-slash", "fa-eye");
  }
}
