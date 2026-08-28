<?php

use App\Core\Csrf;
use App\Models\Ativo;

/** @var array<string,mixed>|null $ativo */
/** @var array<int,array<string,mixed>> $setores */
/** @var array<int,array<string,mixed>> $categorias */

$novo   = $ativo === null;
$acao   = $novo ? url('/ativos/novo') : url('/ativos/' . (int) $ativo['id'] . '/editar');
$v      = static fn (string $campo, string $padrao = ''): string
        => velho($campo) !== '' ? velho($campo) : e($ativo[$campo] ?? $padrao);
?>
<div class="d-flex align-items-center gap-2 mb-3">
  <a class="btn btn-sm btn-outline-secondary" href="<?= $novo ? url('/ativos') : url('/ativos/' . (int) $ativo['id']) ?>">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h1 class="h3 mb-0"><?= $novo ? 'Novo ativo' : 'Editar ativo' ?></h1>
</div>

<form method="post" action="<?= $acao ?>" enctype="multipart/form-data" class="row g-3">
  <?= Csrf::campo() ?>

  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h2 class="h6 text-uppercase text-body-secondary mb-3" style="letter-spacing:.06em">Identificacao</h2>

        <div class="row g-3">
          <div class="col-md-5">
            <label class="form-label" for="patrimonio">Patrimonio <span class="text-danger">*</span></label>
            <input class="form-control text-uppercase font-monospace" id="patrimonio" name="patrimonio"
                   value="<?= $v('patrimonio') ?>" required maxlength="40"
                   pattern="[A-Za-z0-9\-]{3,40}" placeholder="NB-000001">
            <div class="form-text">E o codigo impresso na etiqueta QR. Letras, numeros e hifen.</div>
          </div>

          <div class="col-md-7">
            <label class="form-label" for="nome">Nome do ativo <span class="text-danger">*</span></label>
            <input class="form-control" id="nome" name="nome" value="<?= $v('nome') ?>"
                   required maxlength="140" placeholder="Notebook Dell Latitude 3420">
          </div>

          <div class="col-md-6">
            <label class="form-label" for="categoria_id">Categoria</label>
            <select class="form-select" id="categoria_id" name="categoria_id">
              <option value="">- sem categoria -</option>
              <?php foreach ($categorias as $c) : ?>
                <option value="<?= (int) $c['id'] ?>"
                  <?= (string) ($ativo['categoria_id'] ?? '') === (string) $c['id'] ? 'selected' : '' ?>>
                  <?= e($c['nome']) ?> (<?= e($c['prefixo']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label" for="status">Status</label>
            <select class="form-select" id="status" name="status">
              <?php foreach (Ativo::STATUS as $st) : ?>
                <option value="<?= e($st) ?>"
                  <?= (string) ($ativo['status'] ?? 'disponivel') === $st ? 'selected' : '' ?>>
                  <?= e(statusAtivo($st)['rotulo']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Em manutencao e Baixado bloqueiam a movimentacao do ativo.</div>
          </div>

          <div class="col-md-4">
            <label class="form-label" for="fabricante">Fabricante</label>
            <input class="form-control" id="fabricante" name="fabricante" value="<?= $v('fabricante') ?>" maxlength="80">
          </div>

          <div class="col-md-4">
            <label class="form-label" for="modelo">Modelo</label>
            <input class="form-control" id="modelo" name="modelo" value="<?= $v('modelo') ?>" maxlength="80">
          </div>

          <div class="col-md-4">
            <label class="form-label" for="numero_serie">Numero de serie</label>
            <input class="form-control font-monospace" id="numero_serie" name="numero_serie"
                   value="<?= $v('numero_serie') ?>" maxlength="80">
          </div>

          <div class="col-12">
            <label class="form-label" for="observacoes">Observacoes</label>
            <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?= $v('observacoes') ?></textarea>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <h2 class="h6 text-uppercase text-body-secondary mb-3" style="letter-spacing:.06em">Localizacao</h2>

        <div class="mb-3">
          <label class="form-label" for="setor_id">Setor</label>
          <select class="form-select" id="setor_id" name="setor_id">
            <option value="">- sem setor -</option>
            <?php foreach ($setores as $s) : ?>
              <option value="<?= (int) $s['id'] ?>"
                <?= (string) ($ativo['setor_id'] ?? '') === (string) $s['id'] ? 'selected' : '' ?>>
                <?= e($s['sigla']) ?> &middot; <?= e($s['nome']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (!$novo) : ?>
            <div class="form-text">
              Para mudar de setor, prefira registrar uma movimentacao na ficha do ativo:
              ela fica no historico.
            </div>
          <?php endif; ?>
        </div>

        <div>
          <label class="form-label" for="local_atual">Local</label>
          <input class="form-control" id="local_atual" name="local_atual"
                 value="<?= $v('local_atual') ?>" maxlength="120" placeholder="Bancada 03, Rack 01">
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <h2 class="h6 text-uppercase text-body-secondary mb-3" style="letter-spacing:.06em">Aquisicao</h2>

        <div class="mb-3">
          <label class="form-label" for="data_aquisicao">Data</label>
          <input class="form-control" type="date" id="data_aquisicao" name="data_aquisicao"
                 value="<?= $v('data_aquisicao') ?>">
        </div>

        <div>
          <label class="form-label" for="valor">Valor (R$)</label>
          <input class="form-control" id="valor" name="valor" inputmode="decimal"
                 value="<?= $ativo['valor'] ?? '' ? e(number_format((float) $ativo['valor'], 2, ',', '.')) : velho('valor') ?>"
                 placeholder="4.200,00">
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h2 class="h6 text-uppercase text-body-secondary mb-3" style="letter-spacing:.06em">Foto</h2>

        <?php if (!empty($ativo['foto'])) : ?>
          <p class="small text-body-secondary">Ja existe uma foto. Enviar outra substitui a atual.</p>
        <?php endif; ?>

        <input class="form-control" type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">
        <div class="form-text">JPG, PNG ou WEBP, ate 2 MB.</div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="d-flex gap-2">
      <button class="btn btn-primary" type="submit">
        <i class="bi bi-check-lg me-1"></i><?= $novo ? 'Cadastrar ativo' : 'Salvar alteracoes' ?>
      </button>
      <a class="btn btn-outline-secondary"
         href="<?= $novo ? url('/ativos') : url('/ativos/' . (int) $ativo['id']) ?>">Cancelar</a>
    </div>
  </div>
</form>
