<?php
/**
 * Raiz do projeto no disco e URL base pública (a partir do DOCUMENT_ROOT).
 * Usado em redirecionamentos, nav e links absolutos.
 */
if (!defined('RAIZ_PROJETO')) {
    define('RAIZ_PROJETO', dirname(__DIR__));
}

if (!defined('URL_BASE')) {
    $documento_raiz = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $projeto_real = realpath(RAIZ_PROJETO);

    if ($documento_raiz && $projeto_real && strpos($projeto_real, $documento_raiz) === 0) {
        $relativo = substr($projeto_real, strlen($documento_raiz));
        $relativo = str_replace('\\', '/', $relativo);
        define('URL_BASE', rtrim($relativo, '/'));
    } else {
        define('URL_BASE', '');
    }
}

/**
 * Monta caminho absoluto no site (ex.: /pasta/projeto/publico/painel/pedidos.php).
 */
function url_da_aplicacao(string $caminho): string
{
    $caminho = trim(str_replace('\\', '/', $caminho), '/');
    $base = URL_BASE;

    if ($base === '') {
        return '/' . $caminho;
    }

    return $base . '/' . $caminho;
}

/**
 * Converte caminho salvo no banco (ex.: assets/uploads/x.png) em URL pública.
 */
function url_publica_imagem(?string $guardado): string
{
    $g = trim((string) $guardado);

    if ($g === '') {
        return url_da_aplicacao('recursos/uploads/sem_foto.svg');
    }

    if (preg_match('#^https?://#i', $g)) {
        return $g;
    }

    if (isset($g[0]) && $g[0] === '/') {
        return $g;
    }

    $g = str_replace(['../', '\\'], ['', '/'], $g);
    $g = ltrim($g, '/');
    /* Caminhos legados no banco (assets/uploads) */
    if (str_starts_with($g, 'assets/uploads/')) {
        $g = 'recursos/uploads/' . substr($g, strlen('assets/uploads/'));
    }

    return url_da_aplicacao($g);
}
