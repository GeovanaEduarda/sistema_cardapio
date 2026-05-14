<?php
require_once dirname(__DIR__, 2) . '/configuracao/verificar_sessao.php';
require_once dirname(__DIR__, 2) . '/configuracao/caminhos_da_aplicacao.php';

$nome_usuario = $_SESSION['nome_usuario_logado'] ?? 'Usuário';
$email_usuario = $_SESSION['login_usuario_logado'] ?? 'Não informado';
$nivel_usuario = $_SESSION['nivel_usuario_logado'] ?? 'atendente';
$imagem_sessao = $_SESSION['url_imagem_usuario_logado'] ?? '';

$url_avatar = $imagem_sessao !== '' ? url_publica_imagem($imagem_sessao) : url_da_aplicacao('recursos/uploads/sem_foto.svg');

$mensagem_feedback = '';
if (!empty($_GET['senha'])) {
    switch ($_GET['senha']) {
        case 'ok':
            $mensagem_feedback = 'Senha alterada com sucesso.';
            break;
        case 'erro':
            $mensagem_feedback = 'Não foi possível alterar a senha. Verifique os dados.';
            break;
        case 'erro_atual':
            $mensagem_feedback = 'Senha atual incorreta.';
            break;
        default:
            $mensagem_feedback = '';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_da_aplicacao('recursos/css/estilos.css')); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js" crossorigin="anonymous"></script>
    <title>Meu Perfil | Sistema</title>
</head>

<body class="perfil-page">
    <?php include dirname(__DIR__, 2) . '/componentes/nav.php'; ?>

    <main id="main_perfil_new">
        <?php if ($mensagem_feedback !== ''): ?>
            <p style="margin-bottom:16px;padding:12px;border-radius:8px;background:var(--laranja-claro);color:var(--cor-texto);max-width:450px;width:100%;">
                <?php echo htmlspecialchars($mensagem_feedback); ?>
            </p>
        <?php endif; ?>

        <div class="glass-card">
            <div class="profile-header">
                <div class="avatar-container">
                    <img src="<?php echo htmlspecialchars($url_avatar); ?>" alt="Avatar" class="avatar" width="90" height="90" style="border-radius:50%;object-fit:cover;">
                    <span class="status-badge"></span>
                </div>
                <h2><?php echo htmlspecialchars($nome_usuario); ?></h2>
                <span class="badge-nivel <?php echo htmlspecialchars(preg_replace('/[^a-z0-9_-]/i', '', $nivel_usuario)); ?>">
                    <?php echo htmlspecialchars(strtoupper((string) $nivel_usuario)); ?>
                </span>
            </div>

            <div class="profile-content">
                <div class="info-row">
                    <div class="icon-box"><i class="fa-solid fa-envelope"></i></div>
                    <div class="text-box">
                        <label>E-mail de Acesso</label>
                        <p><?php echo htmlspecialchars($email_usuario); ?></p>
                    </div>
                </div>

                <div class="info-row">
                    <div class="icon-box"><i class="fas fa-id-badge"></i></div>
                    <div class="text-box">
                        <label>Cargo / Função</label>
                        <p><?php echo htmlspecialchars(ucfirst((string) $nivel_usuario)); ?></p>
                    </div>
                </div>
            </div>

            <div class="profile-footer">
                <a href="<?php echo htmlspecialchars(url_da_aplicacao('apis/encerrar_sessao.php')); ?>" class="btn-logout-new">
                    <i class="fas fa-sign-out-alt"></i>
                    Encerrar Sessão
                </a>
            </div>
        </div>

        <div class="senha_perfil">
            <h3>Alterar Senha</h3>
            <form id="form_alterar_senha" class="form-alterar-senha" method="post" action="#">
                <div class="form-group">
                    <label for="senha_atual">Senha Atual</label>
                    <input type="password" id="senha_atual" name="senha_atual" required autocomplete="current-password">
                </div>
                <div class="form-group">
                    <label for="nova_senha">Nova Senha</label>
                    <input type="password" id="nova_senha" name="nova_senha" required minlength="4" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Nova Senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required minlength="4" autocomplete="new-password">
                </div>
                <button type="submit" class="btn-alterar-senha">Alterar Senha</button>
            </form>
        </div>
    </main>

    <script src="<?php echo htmlspecialchars(url_da_aplicacao('recursos/js/scripts_painel.js')); ?>"></script>
    <script>
        window.__APP = { urlLogin: <?php echo json_encode(url_da_aplicacao('index.php'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?> };
    </script>
    <script src="<?php echo htmlspecialchars(url_da_aplicacao('recursos/js/cliente_http.js')); ?>"></script>
    <script>
        const URL_API_PERFIL = <?php echo json_encode(url_da_aplicacao('apis/perfil.php'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?>;

        document.getElementById('form_alterar_senha').addEventListener('submit', async function (e) {
            e.preventDefault();
            const fd = new FormData(e.target);
            const body = {
                senha_atual: fd.get('senha_atual'),
                nova_senha: fd.get('nova_senha'),
                confirmar_senha: fd.get('confirmar_senha')
            };
            const res = await apiFetch(URL_API_PERFIL + '?acao=alterar_senha', {
                method: 'POST',
                body: JSON.stringify(body)
            });
            if (res.success) {
                alert(res.message || 'Senha alterada.');
                e.target.reset();
            } else {
                alert(res.message || 'Não foi possível alterar a senha.');
            }
        });
    </script>
</body>
</html>
