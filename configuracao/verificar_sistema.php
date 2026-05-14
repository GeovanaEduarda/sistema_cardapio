<?php
/**
 * Diagnóstico automático do ambiente e do banco (Etapa 4).
 *
 * Uso em PHP:
 *   require_once __DIR__ . '/verificar_sistema.php';
 *   $relatorio = executar_verificacao_sistema($conexao_pdo); // $conexao_pdo opcional (PDO)
 *
 * Linha de comando (na raiz do projeto):
 *   php configuracao/verificar_sistema.php
 */
declare(strict_types=1);

/**
 * @return array<string, mixed>
 */
function executar_verificacao_sistema(?PDO $conexao_pdo = null): array
{
    $itens = [];
    $okGlobal = true;

    $push = static function (string $grupo, string $nome, bool $ok, string $detalhe = '') use (&$itens, &$okGlobal): void {
        if (!$ok) {
            $okGlobal = false;
        }
        $itens[] = [
            'grupo' => $grupo,
            'nome' => $nome,
            'ok' => $ok,
            'detalhe' => $detalhe,
        ];
    };

    $push('php', 'Versão PHP', version_compare(PHP_VERSION, '8.0.0', '>='), PHP_VERSION);

    $exts = ['pdo', 'pdo_mysql', 'json', 'session', 'mbstring'];
    foreach ($exts as $ext) {
        $push('extensoes', $ext, extension_loaded($ext), extension_loaded($ext) ? 'carregada' : 'ausente');
    }

    $raiz = dirname(__DIR__);
    $uploads = $raiz . DIRECTORY_SEPARATOR . 'recursos' . DIRECTORY_SEPARATOR . 'uploads';
    $push('disco', 'Pasta recursos/uploads gravável', is_dir($uploads) && is_writable($uploads), $uploads);

    $apiDir = $raiz . DIRECTORY_SEPARATOR . 'apis';
    $push('disco', 'Pasta apis existe', is_dir($apiDir), $apiDir);

    if ($conexao_pdo instanceof PDO) {
        try {
            $v = $conexao_pdo->query('SELECT VERSION()')->fetchColumn();
            $push('mysql', 'Conexão MySQL', true, (string) $v);
        } catch (Throwable $e) {
            $push('mysql', 'Conexão MySQL', false, $e->getMessage());
            return [
                'timestamp' => date('c'),
                'ok' => false,
                'itens' => $itens,
            ];
        }

        $tabelasObrigatorias = [
            'usuarios_administradores',
            'categorias',
            'adicionais',
            'itens_do_cardapio',
            'clientes',
            'pedidos',
            'itens_do_pedido',
            'adicionais_do_item_do_pedido',
            'despesas',
            'movimentacoes_do_caixa',
        ];

        foreach ($tabelasObrigatorias as $t) {
            $st = $conexao_pdo->prepare(
                'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
            );
            $st->execute([$t]);
            $existe = (int) $st->fetchColumn() > 0;
            $push('tabelas', $t, $existe, $existe ? 'encontrada' : 'faltando (importe docs/schema_banco_portugues_completo.sql)');
        }

        $mapColunas = [
            'usuarios_administradores' => ['id_usuario', 'login', 'senha', 'nivel', 'ativo'],
            'pedidos' => ['id_pedido', 'status', 'valor_total', 'id_cliente'],
            'movimentacoes_do_caixa' => ['id_movimentacao', 'tipo', 'valor', 'origem'],
        ];
        foreach ($mapColunas as $tabela => $colunas) {
            foreach ($colunas as $col) {
                $st = $conexao_pdo->prepare(
                    'SELECT COUNT(*) FROM information_schema.COLUMNS
                     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
                );
                $st->execute([$tabela, $col]);
                $c = (int) $st->fetchColumn() > 0;
                $push('colunas', $tabela . '.' . $col, $c, $c ? 'ok' : 'ausente');
            }
        }

        $st = $conexao_pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
        );
        $st->execute(['sessoes_do_caixa']);
        if ((int) $st->fetchColumn() > 0) {
            $st = $conexao_pdo->prepare(
                'SELECT COUNT(*) FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
            );
            $st->execute(['movimentacoes_do_caixa', 'id_sessao_do_caixa']);
            $push('etapa3', 'movimentacoes_do_caixa.id_sessao_do_caixa', (int) $st->fetchColumn() > 0, 'Etapa 3 incremental');
        } else {
            $push('etapa3', 'Tabela sessoes_do_caixa', false, 'opcional: importar docs/schema_etapa3_incremental.sql');
        }
    } else {
        $push('mysql', 'Conexão MySQL', false, 'PDO não fornecido (pulando tabelas)');
    }

    return [
        'timestamp' => date('c'),
        'ok' => $okGlobal,
        'itens' => $itens,
    ];
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__)) {
    require_once __DIR__ . '/conexao_banco.php';
    /** @var PDO $conexao_pdo */
    $rel = executar_verificacao_sistema($conexao_pdo);
    echo json_encode($rel, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    exit($rel['ok'] ? 0 : 1);
}
