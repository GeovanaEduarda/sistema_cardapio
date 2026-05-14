document.addEventListener("DOMContentLoaded", () => {
  const formLogin = document.getElementById("form_login");
  const loginError = document.getElementById("loginError");

  formLogin.addEventListener("submit", async (e) => {
    // 1. Evita que a página recarregue
    e.preventDefault();

    // Limpa mensagens de erro anteriores
    loginError.textContent = "";
    loginError.style.display = "none";

    // 2. Coleta os dados do formulário
    // Note que no seu HTML o ID é 'email', mas o PHP espera 'login'
    const login = document.getElementById("email").value;
    const senha = document.getElementById("password").value;

    try {
      // 3. Faz a requisição para o seu arquivo PHP
      // Ajuste o caminho 'api_login.php' para o local real do arquivo
      const response = await fetch("apis/autenticar_usuario.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          login: login,
          senha: senha,
        }),
      });

      const dados = await response.json();

      if (dados.success) {
        // 4. Sucesso: Redireciona o usuário
        // O PHP já criou a $_SESSION, então o redirecionamento é seguro
        window.location.href = "./publico/painel/pedidos.php";
      } else {
        // 5. Erro retornado pela API (401 ou 400)
        exibirErro(dados.message);
      }
    } catch (error) {
      // Erro de rede ou erro inesperado
      console.error("Erro na requisição:", error);
      exibirErro(
        "Não foi possível conectar ao servidor. Tente novamente mais tarde.",
      );
    }
  });

  function exibirErro(mensagem) {
    loginError.textContent = mensagem;
    loginError.style.display = "block";
    loginError.style.color = "red"; // Garante visibilidade se o CSS não tratar
  }
});
