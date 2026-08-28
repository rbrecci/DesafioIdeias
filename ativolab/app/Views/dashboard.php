<?php
/** @var array<string,int> $porStatus */
/** @var array<int,array<string,mixed>> $porSetor */
/** @var array<int,array<string,mixed>> $recentes */
/** @var int $totalAtivos */
/** @var float $valorTotal */
/** @var int $semSetor */
$maiorSetor = 0;
foreach ($porSetor as $s) {
    $maiorSetor = max($maiorSetor, (int) $s['n']);
}
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
  <div>
    <h1 class="h3 mb-1">Painel</h1>
    <p class="text-body-secondary mb-0">Situacao geral do parque de ativos.</p>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="<?= url('/scanner') ?>">
      <i class="bi bi-qr-code-scan me-1"></i>Ler etiqueta
    </a>
    <a class="btn btn-primary" href="<?= url('/ativos') ?>">
      <i class="bi bi-pc-display me-1"></i>Ver ativos
    </a>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-body-secondary small text-uppercase fw-semibold" style="letter-spacing:.06em">Total de ativos</div>
        <div class="display-6 fw-semibold"><?= (int) $totalAtivos ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-body-secondary small text-uppercase fw-semibold" style="letter-spacing:.06em">Valor do parque</div>
        <div class="h3 fw-semibold mb-0 mt-2"><?= e(moedaBr($valorTotal)) ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-body-secondary small text-uppercase fw-semibold" style="letter-spacing:.06em">Em manutencao</div>
        <div class="display-6 fw-semibold text-warning"><?= (int) ($porStatus['em_manutencao'] ?? 0) ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-body-secondary small text-uppercase fw-semibold" style="letter-spacing:.06em">Sem setor</div>
        <div class="display-6 fw-semibold <?= $semSetor > 0 ? 'text-danger' : '' ?>"><?= (int) $semSetor ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent border-0 pt-3">
        <h2 class="h6 mb-0 text-uppercase text-body-secondary" style="letter-spacing:.06em">Por status</h2>
      </div>
      <div class="card-body pt-2">
        <?php foreach ($porStatus as $chave => $qtd) :
            $s   = statusAtivo((string) $chave);
            $pct = $totalAtivos > 0 ? round($qtd * 100 / $totalAtivos) : 0;
        ?>
          <div class="d-flex align-items-center gap-3 mb-3">
            <span class="badge text-bg-<?= e($s['cor']) ?>" style="min-width:110px"><?= e($s['rotulo']) ?></span>
            <div class="progress flex-grow-1" style="height:8px" role="progressbar"
                 aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100">
              <div class="progress-bar bg-<?= e($s['cor']) ?>" style="width:<?= $pct ?>%"></div>
            </div>
            <span class="fw-semibold" style="min-width:2.5rem;text-align:right"><?= (int) $qtd ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent border-0 pt-3">
        <h2 class="h6 mb-0 text-uppercase text-body-secondary" style="letter-spacing:.06em">Ativos por setor</h2>
      </div>
      <div class="card-body pt-2">
        <?php if ($porSetor === []) : ?>
          <p class="text-body-secondary mb-0">Nenhum setor cadastrado ainda.</p>
        <?php else : ?>
          <?php foreach ($porSetor as $s) :
              $pct = $maiorSetor > 0 ? round(((int) $s['n']) * 100 / $maiorSetor) : 0;
          ?>
            <div class="d-flex align-items-center gap-3 mb-3">
              <span class="text-truncate" style="min-width:170px" title="<?= e($s['nome']) ?>">
                <span class="badge text-bg-light border me-1"><?= e($s['sigla']) ?></span>
                <span class="small"><?= e($s['nome']) ?></span>
              </span>
              <div class="progress flex-grow-1" style="height:8px" role="progressbar"
                   aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" style="width:<?= $pct ?>%"></div>
              </div>
              <span class="fw-semibold" style="min-width:2.5rem;text-align:right"><?= (int) $s['n'] ?></span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
        <h2 class="h6 mb-0 text-uppercase text-body-secondary" style="letter-spacing:.06em">Movimentacoes recentes</h2>
      </div>
      <div class="card-body pt-2">
        <?php if ($recentes === []) : ?>
          <p class="text-body-secondary mb-0">Nenhuma movimentacao registrada.</p>
        <?php else : ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead>
                <tr class="text-body-secondary small text-uppercase">
                  <th>Quando</th><th>Ativo</th><th>Trajeto</th><th>Responsavel</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentes as $m) : ?>
                  <tr>
                    <td class="text-nowrap small"><?= e(dataBr($m['criado_em'], true)) ?></td>
                    <td>
                      <a class="text-decoration-none" href="<?= url('/ativos/' . (int) $m['ativo_id']) ?>">
                        <code><?= e($m['patrimonio']) ?></code>
                      </a>
                      <span class="text-body-secondary small d-block d-md-inline ms-md-1"><?= e($m['ativo_nome']) ?></span>
                    </td>
                    <td class="small text-nowrap">
                      <?= e($m['origem_sigla'] ?? 'entrada') ?>
                      <i class="bi bi-arrow-right mx-1 text-body-secondary"></i>
                      <?= e($m['destino_sigla'] ?? '-') ?>
                    </td>
                    <td class="small"><?= e($m['responsavel_nome'] ?? '-') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
