<?php
/**
 * APIs JSON: exige usuário autenticado. Responde 401 no contrato padrão.
 */
require_once __DIR__ . '/resposta_json.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['id_usuario_logado'])) {
    enviar_resposta_json(
        resposta_json(false, 'Sessão expirada ou não autenticado.', (object) [
            'codigo' => 'nao_autenticado',
        ]),
        401
    );
}
