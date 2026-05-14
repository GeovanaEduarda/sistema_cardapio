<?php
/**
 * Contrato padrão das APIs: { "success", "message", "dados" }.
 * "dados" é sempre objeto JSON (stdClass vazio quando não há payload).
 */

/**
 * @param array|object|null $dados Estrutura serializável; null vira objeto vazio.
 * @return array<string, mixed>
 */
function resposta_json(bool $sucesso, string $mensagem, $dados = null): array
{
    if ($dados === null) {
        $dados = new stdClass();
    } elseif (is_array($dados)) {
        $dados = (object) $dados;
    }

    return [
        'success' => $sucesso,
        'message' => $mensagem,
        'dados' => $dados,
    ];
}

function enviar_resposta_json(array $payload, int $codigo_http = 200): void
{
    http_response_code($codigo_http);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}
