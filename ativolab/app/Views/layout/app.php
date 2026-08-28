<?php

use App\Core\Auth;
use App\Core\Config;
use App\Core\Csrf;
use App\Core\Flash;

$usuario = Auth::usuario();
$rota    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$ativoEm = static fn (string $p): string => str_starts_with($rota, rtrim(url($p), '/')) && $p !== '/' ? ' active' : '';
?>
<!doctype html>
<html lang="pt-br" data-bs-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titulo ?? 'AtivoLab') ?> &middot; <?= e(Config::get('app_nome', 'AtivoLab')) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body class="bg-body-tertiary">

<nav class="navbar navbar-expand-lg bg-dark border-bottom border-body" data-bs-theme="dark">
  <div class="container-xl">
    <a class="navbar-brand fw-semibold" href="<?= url('/') ?>">
      <i class="bi bi-box-seam me-1"></i><?= e(Config::get('app_nome', 'AtivoLab')) ?>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navPrincipal"
            aria-controls="navPrincipal" aria-expanded="false" aria-label="Abrir menu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navPrincipal">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link<?= $rota === rtrim(url('/'), '/') || $rota === url('/') ? ' active' : '' ?>" href="<?= url('/') ?>">
            <i class="bi bi-speedometer2 me-1"></i>Painel
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= $ativoEm('/ativos') ?>" href="<?= url('/ativos') ?>">
            <i class="bi bi-pc-display me-1"></i>Ativos
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= $ativoEm('/scanner') ?>" href="<?= url('/scanner') ?>">
            <i class="bi bi-qr-code-scan me-1"></i>Ler etiqueta
          </a>
        </li>
        <?php if (Auth::ehPapel('admin', 'gestor')) : ?>
          <li class="nav-item">
            <a class="nav-link<?= $ativoEm('/etiquetas') ?>" href="<?= url('/etiquetas') ?>">
              <i class="bi bi-tags me-1"></i>Etiquetas
            </a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-gear me-1"></i>Cadastros
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= url('/setores') ?>">Setores</a></li>
              <li><a class="dropdown-item" href="<?= url('/categorias') ?>">Categorias</a></li>
            </ul>
          </li>
        <?php endif; ?>
      </ul>

      <?php if ($usuario !== null) : ?>
        <div class="dropdown">
          <a class="d-flex align-items-center text-decoration-none dropdown-toggle text-white-50"
             href="#" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle fs-5 me-2"></i>
            <span class="small"><?= e($usuario['nome']) ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li class="px-3 py-1 small text-body-secondary">
              <?= e($usuario['email']) ?><br>
              <span class="badge text-bg-secondary mt-1"><?= e(ucfirst($usuario['papel'])) ?></span>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form method="post" action="<?= url('/sair') ?>" class="px-3 py-1">
                <?= Csrf::campo() ?>
                <button class="btn btn-sm btn-outline-danger w-100" type="submit">
                  <i class="bi bi-box-arrow-right me-1"></i>Sair
                </button>
              </form>
            </li>
          </ul>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>

<main class="container-xl py-4">
  <?php foreach (Flash::consumir() as $msg) :
      $cor = ['sucesso' => 'success', 'erro' => 'danger', 'aviso' => 'warning'][$msg['tipo']] ?? 'secondary';
      $ico = ['sucesso' => 'check-circle', 'erro' => 'exclamation-octagon', 'aviso' => 'exclamation-triangle'][$msg['tipo']] ?? 'info-circle';
  ?>
    <div class="alert alert-<?= $cor ?> alert-dismissible fade show d-flex align-items-start" role="alert">
      <i class="bi bi-<?= $ico ?> me-2 mt-1"></i>
      <div><?= e($msg['mensagem']) ?></div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
  <?php endforeach; ?>

  <?= $conteudo ?>
</main>

<footer class="container-xl pb-4">
  <hr class="text-body-secondary">
  <p class="small text-body-secondary mb-0">
    <?= e(Config::get('app_nome', 'AtivoLab')) ?> &middot;
    Projeto Integrador &middot; demanda 12569 &middot; Gestao de Laboratorios e Ativos
  </p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($scripts)) : ?>
  <?php foreach ((array) $scripts as $s) : ?>
    <script src="<?= e($s) ?>"></script>
  <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
