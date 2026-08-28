<?php

use App\Core\Config;
use App\Core\Flash;
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titulo ?? 'AtivoLab') ?> &middot; <?= e(Config::get('app_nome', 'AtivoLab')) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
<div class="container" style="max-width:420px">
  <div class="py-5">
    <?php foreach (Flash::consumir() as $msg) :
        $cor = ['sucesso' => 'success', 'erro' => 'danger', 'aviso' => 'warning'][$msg['tipo']] ?? 'secondary';
    ?>
      <div class="alert alert-<?= $cor ?>" role="alert"><?= e($msg['mensagem']) ?></div>
    <?php endforeach; ?>

    <?= $conteudo ?>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
