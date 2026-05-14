<?php
/**
 * Painel web de diagnóstico (requer sessão). Não exponha publicamente em produção sem proteção extra.
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/configuracao/verificar_sessao.php';
require_once dirname(__DIR__, 2) . '/configuracao/caminhos_da_aplicacao.php';
require_once dirname(__DIR__, 2) . '/configuracao/conexao_banco.php';
require_once dirname(__DIR__, 2) . '/configuracao/verificar_sistema.php';

$relatorio = executar_verificacao_sistema($conexao_pdo);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico do sistema</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(url_da_aplicacao('recursos/css/estilos.css')); ?>">
</head>
<body style="padding:24px;font-family:sans-serif;max-width:960px;margin:0 auto;">
    <h1>Diagnóstico do sistema</h1>
    <p><a href="<?php echo htmlspecialchars(url_da_aplicacao('publico/painel/pedidos.php')); ?>">← Voltar ao painel</a></p>
    <p><strong>Status geral:</strong> <?php echo $relatorio['ok'] ? 'OK' : 'Com pendências'; ?>
        · <?php echo htmlspecialchars($relatorio['timestamp']); ?></p>

    <table style="width:100%;border-collapse:collapse;margin-top:16px;font-size:14px;">
        <thead>
            <tr style="background:#f3f4f6;">
                <th style="text-align:left;padding:8px;border:1px solid #ddd;">Grupo</th>
                <th style="text-align:left;padding:8px;border:1px solid #ddd;">Verificação</th>
                <th style="text-align:left;padding:8px;border:1px solid #ddd;">OK</th>
                <th style="text-align:left;padding:8px;border:1px solid #ddd;">Detalhe</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($relatorio['itens'] as $it): ?>
                <tr>
                    <td style="padding:8px;border:1px solid #ddd;"><?php echo htmlspecialchars((string) $it['grupo']); ?></td>
                    <td style="padding:8px;border:1px solid #ddd;"><?php echo htmlspecialchars((string) $it['nome']); ?></td>
                    <td style="padding:8px;border:1px solid #ddd;"><?php echo $it['ok'] ? 'Sim' : 'Não'; ?></td>
                    <td style="padding:8px;border:1px solid #ddd;"><?php echo htmlspecialchars((string) $it['detalhe']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p style="margin-top:24px;font-size:13px;color:#555;">Documentação: <code>docs/diagnostico_sistema.md</code></p>
</body>
</html>
