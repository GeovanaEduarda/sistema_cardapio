<?php
/**
 * Conexão PDO com MySQL (utf8mb4).
 * Expõe a variável global $conexao_pdo para os scripts que incluem este arquivo.
 */
$servidor_mysql = 'localhost';
$usuario_mysql = 'root';
$senha_mysql = '';
$nome_banco_dados = 'cardapio';

$fonte_dados = sprintf(
    'mysql:host=%s;dbname=%s;charset=utf8mb4',
    $servidor_mysql,
    $nome_banco_dados
);

try {
    $conexao_pdo = new PDO($fonte_dados, $usuario_mysql, $senha_mysql, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $excecao) {
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode([
        'success' => false,
        'message' => 'Falha na conexão com o banco: ' . $excecao->getMessage(),
    ], JSON_UNESCAPED_UNICODE));
}
