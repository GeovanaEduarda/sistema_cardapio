<?php
/**
 * Compatibilidade: redireciona para a API unificada de pedidos (listar).
 */
$_GET['acao'] = 'listar';
require_once __DIR__ . '/pedidos.php';
