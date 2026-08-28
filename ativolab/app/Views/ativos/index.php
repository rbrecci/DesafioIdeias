<?php

use App\Core\Auth;

/** @var array{itens:array,total:int,paginas:int,pagina:int} $lista */
/** @var array<string,string> $filtros */
/** @var array<int,array<string,mixed>> $setores */
/** @var array<int,array<string,mixed>> $categorias */

$querySem = static function (array $extra) use ($filtros): string {
    return '?' . http_build_query(array_filter(array_merge($filtros, $extra), static fn ($v) => $v !== '' && $v !== null));
};
$temFiltro = array_filter($filtros, static fn ($v) => $v !== '') !== [];
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
  <div>
    <h1 class="h3 mb-1">Ativos</h1>
    <p class="text-body-secondary mb-0">
      <?= (int) $lista['total'] ?> <?= $lista['total'] === 1 ? 'ativo' : 'ativos' ?>
      <?= $temFiltro ? 'com os filtros aplicados' : 'cadastrados' ?>.
    </p>
  </div>
  <?php if (Auth::ehPapel('admin', 'gestor')) : ?>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="<?= url('/etiquetas') . $querySem([]) ?>">
        <i class="bi bi-tags me-1"></i>Etiquetas
      </a>
      <a class="btn btn-primary" href="<?= url('/ativos/novo') ?>">
        <i class="bi bi-plus-lg me-1"></i>Novo ativo
      </a>
    </div>
  <?php endif; ?>
</div>

<div class="card border-0 shadow-sm mb-3">
  <div class="card-body">
    <form method="get" action="<?= url('/ativos') ?>" class="row g-2 align-items-end">
      <div class="col-12 col-lg-4">
        <label class="form-label small text-body-secondary mb-1" for="busca">Buscar</label>
        <input class="form-control" type="search" id="busca" name="busca"
               value="<?= e($filtros['busca']) ?>" placeholder="Patrimonio, nome, serie ou modelo">
      </div>
      <div class="col-6 col-lg-2">
        <label class="form-label small text-body-secondary mb-1" for="setor">Setor</label>
        <select class="form-select" id="setor" name="setor">
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
        <select class="form-select" id="categoria" name="categoria">
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
        <select class="form-select" id="status" name="status">
          <option value="">Todos</option>
          <?php foreach (App\Models\Ativo::STATUS as $st) : ?>
            <option value="<?= e($st) ?>" <?= $filtros['status'] === $st ? 'selected' : '' ?>>
              <?= e(statusAtivo($st)['rotulo']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-lg-2 d-flex gap-2">
        <button class="btn btn-primary flex-grow-1" type="submit">
          <i class="bi bi-funnel me-1"></i>Filtrar
        </button>
        <?php if ($temFiltro) : ?>
          <a class="btn btn-outline-secondary" href="<?= url('/ativos') ?>" title="Limpar filtros">
            <i class="bi bi-x-lg"></i>
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <?php if ($lista['itens'] === []) : ?>
    <div class="card-body text-center py-5">
      <i class="bi bi-inbox display-5 text-body-secondary"></i>
      <p class="mt-3 mb-0 text-body-secondary">
        <?= $temFiltro ? 'Nenhum ativo corresponde aos filtros.' : 'Nenhum ativo cadastrado ainda.' ?>
      </p>
    </div>
  <?php else : ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr class="small text-uppercase text-body-secondary">
            <th>Patrimonio</th>
            <th>Ativo</th>
            <th class="d-none d-md-table-cell">Categoria</th>
            <th>Setor</th>
            <th class="d-none d-lg-table-cell">Local</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($lista['itens'] as $a) :
              $s = statusAtivo((string) $a['status']);
          ?>
            <tr class="cursor-pointer" onclick="location.href='<?= url('/ativos/' . (int) $a['id']) ?>'">
              <td><code class="fw-semibold"><?= e($a['patrimonio']) ?></code></td>
              <td>
                <a class="text-decoration-none fw-medium" href="<?= url('/ativos/' . (int) $a['id']) ?>">
                  <?= e($a['nome']) ?>
                </a>
                <?php if (!empty($a['fabricante']) || !empty($a['modelo'])) : ?>
                  <div class="small text-body-secondary">
                    <?= e(trim($a['fabricante'] . ' ' . $a['modelo'])) ?>
                  </div>
                <?php endif; ?>
              </td>
              <td class="d-none d-md-table-cell small"><?= e($a['categoria_nome'] ?? '-') ?></td>
              <td>
                <?php if (!empty($a['setor_sigla'])) : ?>
                  <span class="badge text-bg-light border"><?= e($a['setor_sigla']) ?></span>
                <?php else : ?>
                  <span class="text-body-secondary small">-</span>
                <?php endif; ?>
              </td>
              <td class="d-none d-lg-table-cell small text-body-secondary"><?= e($a['local_atual'] ?? '-') ?></td>
              <td><span class="badge text-bg-<?= e($s['cor']) ?>"><?= e($s['rotulo']) ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($lista['paginas'] > 1) : ?>
      <div class="card-footer bg-transparent">
        <nav aria-label="Paginacao">
          <ul class="pagination pagination-sm mb-0 justify-content-center flex-wrap">
            <?php for ($p = 1; $p <= $lista['paginas']; $p++) : ?>
              <li class="page-item <?= $p === $lista['pagina'] ? 'active' : '' ?>">
                <a class="page-link" href="<?= url('/ativos') . $querySem(['pagina' => $p]) ?>"><?= $p ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
