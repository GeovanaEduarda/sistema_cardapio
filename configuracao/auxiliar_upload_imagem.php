<?php
/**
 * Upload seguro de imagem para recursos/uploads.
 */
require_once __DIR__ . '/caminhos_da_aplicacao.php';

/** Tamanho máximo padrão: 2 MB */
define('TAMANHO_MAXIMO_UPLOAD_IMAGEM', 2 * 1024 * 1024);

/**
 * @param array $arquivo Elemento de $_FILES (ex.: $_FILES['foto_item'])
 * @return array{sucesso: bool, caminho_relativo?: string, mensagem?: string}
 */
function salvar_upload_imagem_seguro(array $arquivo, int $tamanho_maximo = TAMANHO_MAXIMO_UPLOAD_IMAGEM): array
{
    if (($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['sucesso' => false, 'mensagem' => 'Nenhum arquivo enviado ou erro no upload.'];
    }

    if (($arquivo['size'] ?? 0) > $tamanho_maximo) {
        return ['sucesso' => false, 'mensagem' => 'Arquivo muito grande (máximo 2 MB).'];
    }

    $tmp = $arquivo['tmp_name'] ?? '';
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return ['sucesso' => false, 'mensagem' => 'Upload inválido.'];
    }

    if (!class_exists('finfo')) {
        return ['sucesso' => false, 'mensagem' => 'Extensão fileinfo do PHP necessária para validar imagens.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);

    $mapa_mime_ext = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($mapa_mime_ext[$mime])) {
        return ['sucesso' => false, 'mensagem' => 'Tipo de imagem não permitido (use JPG, PNG ou WEBP).'];
    }

    $ext = $mapa_mime_ext[$mime];
    $nome_seguro = 'img_' . bin2hex(random_bytes(12)) . '.' . $ext;

    $dir = RAIZ_PROJETO . DIRECTORY_SEPARATOR . 'recursos' . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        return ['sucesso' => false, 'mensagem' => 'Não foi possível criar a pasta de uploads.'];
    }

    $destino_fs = $dir . DIRECTORY_SEPARATOR . $nome_seguro;
    if (!move_uploaded_file($tmp, $destino_fs)) {
        return ['sucesso' => false, 'mensagem' => 'Falha ao salvar o arquivo.'];
    }

    return [
        'sucesso' => true,
        'caminho_relativo' => 'recursos/uploads/' . $nome_seguro,
    ];
}
