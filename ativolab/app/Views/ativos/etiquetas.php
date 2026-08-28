<?php

use App\Core\Config;
use App\Models\Ativo;

/** @var array<int,array<string,mixed>> $ativos */
/** @var int $total */
/** @var array<string,string> $filtros */
/** @var array<int,array<string,mixed>> $setores */
/** @var array<int,array<string,mixed>> $categorias */

$org = (string) Config::get('app_nome', 'AtivoLab');
?>
<div class="nao-imprimir bg-body-tertiary border-bottom">
  <div class="container-xl py-3">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
      <div class="d-flex align-items-center gap-2">
        <a class="btn btn-sm btn-outline-secondary" href="<?= url('/ativos') ?>"><i class="bi bi-arrow-left"></i></a>
        <div>
          <h1 class="h5 mb-0">Folha de etiquetas</h1>
          <div class="small text-body-secondary">
            <span id="contador"><?= count($ativos) ?></span> etiquetas
            <?= $total > count($ativos) ? ' (de ' . (int) $total . ' ativos; limite de 200 por folha)' : '' ?>
          </div>
        </div>
      </div>

      <button class="btn btn-primary" type="button" onclick="window.print()">
        <i class="bi bi-printer me-1"></i>Imprimir
      </button>
    </div>

    <div id="avisoQr" class="alert alert-danger d-none">
      Nao foi possivel carregar a biblioteca de QR. Verifique a conexao e recarregue a pagina
      antes de imprimir &mdash; as etiquetas sairiam em branco.
    </div>

    <form method="get" action="<?= url('/etiquetas') ?>" class="row g-2 align-items-end">
      <div class="col-12 col-lg-4">
        <label class="form-label small text-body-secondary mb-1" for="busca">Buscar</label>
        <input class="form-control form-control-sm" type="search" id="busca" name="busca"
               value="<?= e($filtros['busca']) ?>" placeholder="Patrimonio, nome ou modelo">
      </div>
      <div class="col-6 col-lg-2">
        <label class="form-label small text-body-secondary mb-1" for="setor">Setor</label>
        <select class="form-select form-select-sm" id="setor" name="setor">
          <option value="">Todos</option>
          <?php foreach ($setores as $s) : ?>
            <option value="<?= (int) $s['id'] ?>" <?= $filtros['setor'] === (string) $s['id'] ? 'selected' : '' ?>>
              <?= e($s['sigla']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-lg-2">
        <label class="form-label small text-body-secondary mb-1" for="categoria">Categoria</label>
        <select class="form-select form-select-sm" id="categoria" name="categoria">
          <option value="">Todas</option>
          <?php foreach ($categorias as $c) : ?>
            <option value="<?= (int) $c['id'] ?>" <?= $filtros['categoria'] === (string) $c['id'] ? 'selected' : '' ?>>
              <?= e($c['nome']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-lg-2">
        <label class="form-label small text-body-secondary mb-1" for="status">Status</label>
        <select class="form-select form-select-sm" id="status" name="status">
          <option value="">Todos</option>
          <?php foreach (Ativo::STATUS as $st) : ?>
            <option value="<?= e($st) ?>" <?= $filtros['status'] === $st ? 'selected' : '' ?>>
              <?= e(statusAtivo($st)['rotulo']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-lg-2">
        <button class="btn btn-sm btn-outline-primary w-100" type="submit">
          <i class="bi bi-funnel me-1"></i>Aplicar
        </button>
      </div>
    </form>

    <p class="small text-body-secondary mt-3 mb-0">
      <i class="bi bi-info-circle me-1"></i>
      Imprima em papel adesivo A4. Nas opcoes de impressao, desative
      &ldquo;ajustar a pagina&rdquo; e mantenha a escala em 100% &mdash; o QR precisa sair no tamanho fisico correto.
    </p>
  </div>
</div>

<div class="folha mt-3">
  <?php if ($ativos === []) : ?>
    <p class="text-center text-body-secondary py-5 nao-imprimir">
      Nenhum ativo corresponde aos filtros.
    </p>
  <?php else : ?>
    <div class="grade">
      <?php foreach ($ativos as $a) : ?>
        <div class="etiqueta">
          <div class="qr" data-patrimonio="<?= e($a['patrimonio']) ?>" data-base="<?= e(url('/p/')) ?>"></div>
          <div class="txt">
            <div class="pat"><?= e($a['patrimonio']) ?></div>
            <div class="nom"><?= e($a['nome']) ?></div>
            <div class="set">
              <?= e($a['setor_sigla'] ?? 'sem setor') ?>
              <?= !empty($a['local_atual']) ? ' &middot; ' . e($a['local_atual']) : '' ?>
            </div>
            <div class="org"><?= e(mb_strtoupper($org)) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
