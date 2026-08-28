<?php

declare(strict_types=1);

/**
 * AtivoLab - front controller.
 *
 * Todo request passa por aqui (ver .htaccess). Este arquivo faz quatro
 * coisas e mais nada: carrega config, prepara a sessao, registra as rotas
 * e despacha.
 */

use App\Core\Config;
use App\Core\Router;

// ------------------------------------------------------------------ autoload
// Precisa vir antes de qualquer uso de classe App\, inclusive Config.
spl_autoload_register(static function (string $classe): void {
    $prefixo = 'App\\';

    if (!str_starts_with($classe, $prefixo)) {
        return;
    }

    $relativo = str_replace('\\', '/', substr($classe, strlen($prefixo)));
    $arquivo  = __DIR__ . '/app/' . $relativo . '.php';

    if (is_file($arquivo)) {
        require $arquivo;
    }
});

require __DIR__ . '/app/Core/helpers.php';

// ------------------------------------------------------------- configuracao
$arquivoConfig = __DIR__ . '/config/config.php';

if (!is_file($arquivoConfig)) {
    http_response_code(500);
    exit(
        'Configuracao ausente. Copie config/config.example.php para '
        . 'config/config.php e preencha os dados do banco.'
    );
}

Config::carregar(require $arquivoConfig);

$debug = (bool) Config::get('debug', false);
ini_set('display_errors', $debug ? '1' : '0');
error_reporting($debug ? E_ALL : E_ALL & ~E_DEPRECATED);

// -------------------------------------------------------------------- sessao
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ($_SERVER['SERVER_PORT'] ?? '') === '443'
    || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => $https,   // so trafega o cookie em HTTPS quando ele existe
    'httponly' => true,     // JavaScript nao enxerga o cookie de sessao
    'samesite' => 'Lax',    // corta a maior parte dos ataques CSRF entre sites
]);

session_name('ativolab');
session_start();

// ---------------------------------------------------------------- cabecalhos
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');

// -------------------------------------------------------------------- rotas
$r = new Router();

$r->get('/login',  [App\Controllers\AuthController::class, 'formulario']);
$r->post('/login', [App\Controllers\AuthController::class, 'entrar']);
$r->post('/sair',  [App\Controllers\AuthController::class, 'sair']);

$r->get('/', [App\Controllers\DashboardController::class, 'index']);

$r->get('/ativos',                  [App\Controllers\AtivoController::class, 'index']);
$r->get('/ativos/novo',             [App\Controllers\AtivoController::class, 'formularioCriar']);
$r->post('/ativos/novo',            [App\Controllers\AtivoController::class, 'salvar']);
$r->get('/ativos/{id}',             [App\Controllers\AtivoController::class, 'ver']);
$r->get('/ativos/{id}/editar',      [App\Controllers\AtivoController::class, 'formularioEditar']);
$r->post('/ativos/{id}/editar',     [App\Controllers\AtivoController::class, 'atualizar']);
$r->post('/ativos/{id}/movimentar', [App\Controllers\AtivoController::class, 'movimentar']);
$r->post('/ativos/{id}/excluir',    [App\Controllers\AtivoController::class, 'excluir']);

// Destino da leitura de QR. Curto de proposito: cabe em etiqueta pequena.
$r->get('/p/{patrimonio}', [App\Controllers\AtivoController::class, 'porPatrimonio']);

$r->get('/scanner',   [App\Controllers\ScannerController::class, 'index']);
$r->get('/etiquetas', [App\Controllers\EtiquetaController::class, 'index']);

$r->get('/setores',              [App\Controllers\CadastroController::class, 'setores']);
$r->post('/setores',             [App\Controllers\CadastroController::class, 'salvarSetor']);
$r->post('/setores/{id}/excluir',[App\Controllers\CadastroController::class, 'excluirSetor']);

$r->get('/categorias',              [App\Controllers\CadastroController::class, 'categorias']);
$r->post('/categorias',             [App\Controllers\CadastroController::class, 'salvarCategoria']);
$r->post('/categorias/{id}/excluir',[App\Controllers\CadastroController::class, 'excluirCategoria']);

// ------------------------------------------------------------------ despacho
try {
    $r->despachar(
        $_SERVER['REQUEST_METHOD'] ?? 'GET',
        $_SERVER['REQUEST_URI'] ?? '/'
    );
} catch (Throwable $e) {
    http_response_code(500);

    if ($debug) {
        echo '<pre style="padding:20px;font:13px monospace;white-space:pre-wrap">';
        echo e($e->getMessage()) . "\n\n" . e($e->getTraceAsString());
        echo '</pre>';
        exit;
    }

    error_log('[AtivoLab] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
    echo 'Ocorreu um erro inesperado. Tente novamente em instantes.';
}
