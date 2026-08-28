<?php

use App\Core\Auth;
use App\Core\Csrf;

/** @var array<int,array<string,mixed>> $setores */
?>
<div class="d-flex align-items-center gap-2 mb-3">
  <a class="btn btn-sm btn-outline-secondary" href="<?= url('/') ?>"><i class="bi bi-arrow-left"></i></a>
  <h1 class="h3 mb-0">Setores</h1>
</div>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h2 class="h6 text-uppercase text-body-secondary mb-3" style="letter-spacing:.06em" id="tituloForm">
          Novo setor
        </h2>

        <form method="post" action="<?= url('/setores') ?>" id="formSetor">
          <?= Csrf::campo() ?>
          <input type="hidden" name="id" id="setorId" value="">

          <div class="mb-2">
            <label class="form-label small" for="nome">Nome</label>
            <input class="form-control" id="nome" name="nome" value="<?= velho('nome') ?>"
                   required maxlength="80" placeholder="Laboratorio de Informatica">
          </div>

          <div class="mb-3">
            <label class="form-label small" for="sigla">Sigla</label>
            <input class="form-control text-uppercase font-monospace" id="sigla" name="sigla"
                   value="<?= velho('sigla') ?>" required maxlength="12"
                   pattern="[A-Za-z0-9\-]{2,12}" placeholder="LAB-INFO">
            <div class="form-text">Aparece na etiqueta e nas listagens.</div>
          </div>

          <div class="d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" type="submit">Salvar</button>
            <button class="btn btn-outline-secondary d-none" type="button" id="btnCancelar">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <?php if ($setores === []) : ?>
        <div class="card-body text-center py-5 text-body-secondary">Nenhum setor cadastrado.</div>
      <?php else : ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr class="small text-uppercase text-body-secondary">
                <th>Sigla</th><th>Nome</th><th class="text-center">Ativos</th><th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($setores as $s) : ?>
                <tr>
                  <td><span class="badge text-bg-light border"><?= e($s['sigla']) ?></span></td>
                  <td><?= e($s['nome']) ?></td>
                  <td class="text-center">
                    <?php if ((int) $s['total_ativos'] > 0) : ?>
                      <a class="text-decoration-none" href="<?= url('/ativos?setor=' . (int) $s['id']) ?>">
                        <?= (int) $s['total_ativos'] ?>
                      </a>
                    <?php else : ?>
                      <span class="text-body-secondary">0</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end text-nowrap">
                    <button class="btn btn-sm btn-outline-secondary btn-editar"
                            data-id="<?= (int) $s['id'] ?>"
                            data-nome="<?= e($s['nome']) ?>"
                            data-sigla="<?= e($s['sigla']) ?>"
                            title="Editar">
                      <i class="bi bi-pencil"></i>
                    </button>

                    <?php if (Auth::ehPapel('admin')) : ?>
                      <form method="post" action="<?= url('/setores/' . (int) $s['id'] . '/excluir') ?>"
                            class="d-inline"
                            onsubmit="return confirm('Excluir o setor <?= e($s['sigla']) ?>?')">
                        <?= Csrf::campo() ?>
                        <button class="btn btn-sm btn-outline-danger"
                                <?= (int) $s['total_ativos'] > 0 ? 'disabled title="Tem ativos vinculados"' : 'title="Excluir"' ?>>
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
(function () {
  var form   = document.getElementById('formSetor');
  var titulo = document.getElementById('tituloForm');
  var campoId = document.getElementById('setorId');
  var cancelar = document.getElementById('btnCancelar');

  document.querySelectorAll('.btn-editar').forEach(function (b) {
    b.addEventListener('click', function () {
      campoId.value = b.dataset.id;
      form.nome.value = b.dataset.nome;
      form.sigla.value = b.dataset.sigla;
      titulo.textContent = 'Editar setor';
      cancelar.classList.remove('d-none');
      form.nome.focus();
    });
  });

  cancelar.addEventListener('click', function () {
    form.reset();
    campoId.value = '';
    titulo.textContent = 'Novo setor';
    cancelar.classList.add('d-none');
  });
})();
</script>
