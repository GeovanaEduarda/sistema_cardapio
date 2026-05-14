<?php
/**
 * Teste leve do contrato JSON (sem PHPUnit).
 * Execução: php testes/contrato_json_test.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/configuracao/resposta_json.php';

$erros = [];

$r = resposta_json(true, 'ok', ['x' => 1]);
if (!isset($r['success'], $r['message'], $r['dados']) || $r['success'] !== true) {
    $erros[] = 'resposta_json sucesso incompleto';
}
if (!is_object($r['dados'])) {
    $erros[] = 'dados deveria ser objeto';
}

$r2 = resposta_json(false, 'falha', null);
if (!is_object($r2['dados']) || $r2['success'] !== false) {
    $erros[] = 'resposta_json falha com dados null';
}

if ($erros) {
    fwrite(STDERR, implode("\n", $erros) . "\n");
    exit(1);
}

echo "contrato_json_test: OK\n";
exit(0);
