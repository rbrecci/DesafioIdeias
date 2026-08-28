<?php

use App\Core\Auth;
use App\Core\Csrf;
use App\Models\Ativo;

/** @var array<string,mixed> $ativo */
/** @var array<int,array<string,mixed>> $historico */
/** @var array<int,array<string,mixed>> $auditoria */
/** @var array<int,array<string,mixed>> $setores */

$s          = statusAtivo((string) $ativo['status']);
$podeMover  = Ativo::podeMovimentar($ativo);
$podeEditar = Auth::ehPapel('admin', 'gestor');
?>
<div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
  <div class="d-flex align-items-center gap-2">
    <a class="btn btn-sm btn-outline-secondary" href="<?= url('/ativos') ?>"><i class="bi bi-arrow-left"></i></a>
    <div>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <code class="fs-5 fw-semibold"><?= e($ativo['patrimonio']) ?></code>
        <span class="badge text-bg-<?= e($s['cor']) ?>"><?= e($s['rotulo']) ?></span>
      </div>
      <h1 class="h4 mb-0 mt-1"><?= e($ativo['nome']) ?></h1>
    </div>
  </div>

  <?php if ($podeEditar) : ?>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="<?= url('/ativos/' . (int) $ativo['id'] . '/editar') ?>">
        <i class="bi bi-pencil me-1"></i>Editar
      </a>
      <?php if (Auth::ehPapel('admin')) : ?>
        <button class="btn btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#modalExcluir">
          <i class="bi bi-trash"></i>
        </button>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <h2 class="h6 text-uppercase text-body-secondary mb-3" style="letter-spacing:.06em">Dados do ativo</h2>
        <dl class="row mb-0 small">
          <dt class="col-5 col-sm-4 text-body-secondary fw-normal">Categoria</dt>
          <dd class="col-7 col-sm-8"><?= e($ativo['categoria_nome'] ?? '-') ?></dd>

          <dt class="col-5 col-sm-4 text-body-secondary fw-normal">Setor</dt>
          <dd class="col-7 col-sm-8">
            <?php if (!empty($ativo['setor_nome'])) : ?>
              <span class="badge text-bg-light border"><?= e($ativo['setor_sigla']) ?></span>
              <?= e($ativo['setor_nome']) ?>
            <?php else : ?>-<?php endif; ?>
          </dd>

          <dt class="col-5 col-sm-4 text-body-secondary fw-normal">Local</dt>
          <dd class="col-7 col-sm-8"><?= e($ativo['local_atual'] ?? '-') ?></dd>

          <dt class="col-5 col-sm-4 text-body-secondary fw-normal">Fabricante / modelo</dt>
          <dd class="col-7 col-sm-8"><?= e(trim(($ativo['fabricante'] ?? '') . ' ' . ($ativo['modelo'] ?? '')) ?: '-') ?></dd>

          <dt class="col-5 col-sm-4 text-body-secondary fw-normal">Numero de serie</dt>
          <dd class="col-7 col-sm-8"><code><?= e($ativo['numero_serie'] ?? '-') ?></code></dd>

          <dt class="col-5 col-sm-4 text-body-secondary fw-normal">Aquisicao</dt>
          <dd class="col-7 col-sm-8"><?= e(dataBr($ativo['data_aquisicao'])) ?></dd>

          <dt class="col-5 col-sm-4 text-body-secondary fw-normal">Valor</dt>
          <dd class="col-7 col-sm-8"><?= e(moedaBr($ativo['valor'])) ?></dd>

          <dt class="col-5 col-sm-4 text-body-secondary fw-normal">Cadastrado por</dt>
          <dd class="col-7 col-sm-8">
            <?= e($ativo['criador_nome'] ?? '-') ?>
            <span class="text-body-secondary">em <?= e(dataBr($ativo['criado_em'], true)) ?></span>
          </dd>

          <?php if (!empty($ativo['observacoes'])) : ?>
            <dt class="col-12 text-body-secondary fw-normal mt-2">Observacoes</dt>
            <dd class="col-12 mb-0"><?= nl2br(e($ativo['observacoes'])) ?></dd>
          <?php endif; ?>
        </dl>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent border-0 pt-3">
        <h2 class="h6 mb-0 text-uppercase text-body-secondary" style="letter-spacing:.06em">
          Historico de movimentacao
        </h2>
      </div>
      <div class="card-body pt-2">
        <?php if ($historico === []) : ?>
          <p class="text-body-secondary small mb-0">Nenhuma movimentacao registrada.</p>
        <?php else : ?>
          <ol class="list-unstyled mb-0 linha-tempo">
            <?php foreach ($historico as $m) : ?>
              <li class="pb-3">
                <div class="small text-body-secondary"><?= e(dataBr($m['criado_em'], true)) ?></div>
                <div>
                  <?php if ($m['origem_sigla'] === null) : ?>
                    <span class="badge text-bg-success-subtle text-success-emphasis border border-success-subtle">entrada</span>
                  <?php else : ?>
                    <span class="badge text-bg-light border"><?= e($m['origem_sigla']) ?></span>
                  <?php endif; ?>
                  <i class="bi bi-arrow-right mx-1 text-body-secondary"></i>
                  <span class="badge text-bg-light border"><?= e($m['destino_sigla'] ?? '-') ?></span>
                  <?php if (!empty($m['local_destino'])) : ?>
                    <span class="small text-body-secondary ms-1"><?= e($m['local_destino']) ?></span>
                  <?php endif; ?>
                </div>
                <div class="small text-body-secondary">
                  por <?= e($m['responsavel_nome'] ?? 'sistema') ?>
                  <?php if (!empty($m['observacao'])) : ?>
                    &middot; <?= e($m['observacao']) ?>
                  <?php endif; ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ol>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body text-center">
        <h2 class="h6 text-uppercase text-body-secondary mb-3" style="letter-spacing:.06em">Etiqueta</h2>
        <div id="qr" class="d-inline-block border rounded p-2 bg-white"
             data-valor="<?= e($ativo['patrimonio']) ?>"></div>
        <div class="small text-body-secondary mt-2">
          Aponte a camera para abrir esta ficha.
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <h2 class="h6 text-uppercase text-body-secondary mb-3" style="letter-spacing:.06em">Movimentar</h2>

        <?php if (!$podeMover) : ?>
          <div class="alert alert-warning small mb-0 d-flex gap-2">
            <i class="bi bi-lock mt-1"></i>
            <div>
              Ativo com status <strong><?= e($s['rotulo']) ?></strong> nao circula entre setores.
              Altere o status para liberar a movimentacao.
            </div>
          </div>
        <?php else : ?>
          <form method="post" action="<?= url('/ativos/' . (int) $ativo['id'] . '/movimentar') ?>">
            <?= Csrf::campo() ?>

            <div class="mb-2">
              <label class="form-label small" for="setor_destino_id">Setor de destino</label>
              <select class="form-select" id="setor_destino_id" name="setor_destino_id" required>
                <?php foreach ($setores as $st) : ?>
                  <option value="<?= (int) $st['id'] ?>"
                    <?= (string) ($ativo['setor_id'] ?? '') === (string) $st['id'] ? 'selected' : '' ?>>
                    <?= e($st['sigla']) ?> &mdash; <?= e($st['nome']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-2">
              <label class="form-label small" for="local_destino">Local</label>
              <input class="form-control" id="local_destino" name="local_destino"
                     value="<?= e($ativo['local_atual'] ?? '') ?>" maxlength="120">
            </div>

            <div class="mb-3">
              <label class="form-label small" for="observacao">Observacao</label>
              <input class="form-control" id="observacao" name="observacao" maxlength="255"
                     placeholder="Motivo da movimentacao">
            </div>

            <button class="btn btn-primary w-100" type="submit">
              <i class="bi bi-arrow-left-right me-1"></i>Registrar movimentacao
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent border-0 pt-3">
        <h2 class="h6 mb-0 text-uppercase text-body-secondary" style="letter-spacing:.06em">
          Trilha de auditoria
        </h2>
      </div>
      <div class="card-body pt-2">
        <?php if ($auditoria === []) : ?>
          <p class="text-body-secondary small mb-0">Sem registros.</p>
        <?php else : ?>
          <div class="small" style="max-height:260px;overflow-y:auto">
            <?php foreach ($auditoria as $a) : ?>
              <div class="d-flex gap-2 pb-2 mb-2 border-bottom">
                <div class="text-body-secondary text-nowrap" style="min-width:96px">
                  <?= e(dataBr($a['criado_em'], true)) ?>
                </div>
                <div>
                  <strong><?= e($a['usuario_nome'] ?? 'sistema') ?></strong>
                  <?= e($a['acao']) ?>
                  <?php if (!empty($a['campo'])) : ?>
                    <code><?= e($a['campo']) ?></code>
                    <?php if ($a['valor_anterior'] !== null || $a['valor_novo'] !== null) : ?>
                      <div class="text-body-secondary">
                        <span class="text-decoration-line-through"><?= e($a['valor_anterior'] ?? 'vazio') ?></span>
                        <i class="bi bi-arrow-right mx-1"></i>
                        <?= e($a['valor_novo'] ?? 'vazio') ?>
                      </div>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if (Auth::ehPapel('admin')) : ?>
  <div class="modal fade" id="modalExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Excluir ativo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <p class="mb-1">Excluir <code><?= e($ativo['patrimonio']) ?></code> &mdash; <?= e($ativo['nome']) ?>?</p>
          <p class="small text-body-secondary mb-0">
            O historico de movimentacao deste ativo tambem sera removido. A trilha de auditoria permanece.
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
          <form method="post" action="<?= url('/ativos/' . (int) $ativo['id'] . '/excluir') ?>">
            <?= Csrf::campo() ?>
            <button class="btn btn-danger" type="submit">Excluir definitivamente</button>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
(function () {
  var alvo = document.getElementById('qr');
  if (!alvo || typeof QRCode === 'undefined') { return; }

  var destino = location.origin + '<?= url('/p/') ?>' + encodeURIComponent(alvo.dataset.valor);

  QRCode.toString(destino, { type: 'svg', margin: 0, width: 168 }, function (err, svg) {
    if (err) { alvo.textContent = 'Nao foi possivel gerar o QR.'; return; }
    alvo.innerHTML = svg;
  });
})();
</script>
