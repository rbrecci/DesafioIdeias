<?php

/**
 * Roteador para o servidor embutido do PHP, usado so em desenvolvimento:
 *
 *   php -S localhost:8000 servidor-local.php
 *
 * Em producao quem faz esse papel e o .htaccess. Este arquivo nao tem
 * nenhum efeito na hospedagem.
 */

$caminho = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

// arquivos reais (css, js, imagens) o proprio servidor entrega
if ($caminho !== '/' && is_file(__DIR__ . $caminho)) {
    return false;
}

require __DIR__ . '/index.php';
