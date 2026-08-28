<?php

use App\Core\Auth;
use App\Core\Csrf;

/** @var array<int,array<string,mixed>> $categorias */
?>
<div class="d-flex align-items-center gap-2 mb-3">
  <a class="btn btn-sm btn-outline-secondary" href="<?= url('/') ?>"><i class="bi bi-arrow-left"></i></a>
  <h1 class="h3 mb-0">Categorias</h1>
</div>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h2 class="h6 text-uppercase text-body-secondary mb-3" style="letter-spacing:.06em" id="tituloForm">
          Nova categoria
        </h2>

        <form method="post" action="<?= url('/categorias') ?>" id="formCategoria">
          <?= Csrf::campo() ?>
          <input type="hidden" name="id" id="categoriaId" value="">

          <div class="mb-2">
            <label class="form-label small" for="nome">Nome</label>
            <input class="form-control" id="nome" name="nome" value="<?= velho('nome') ?>"
                   required maxlength="80" placeholder="Notebook">
          </div>

          <div class="mb-3">
            <label class="form-label small" for="prefixo">Prefixo do patrimonio</label>
            <input class="form-control text-uppercase font-monospace" id="prefixo" name="prefixo"
                   value="<?= velho('prefixo') ?>" required maxlength="8"
                   pattern="[A-Za-z0-9]{2,8}" placeholder="NB">
            <div class="form-text">Vira o inicio do codigo: <code>NB-000001</code>.</div>
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
      <?php if ($categorias === []) : ?>
        <div class="card-body text-center py-5 text-body-secondary">Nenhuma categoria cadastrada.</div>
      <?php else : ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr class="small text-uppercase text-body-secondary">
                <th>Prefixo</th><th>Nome</th><th class="text-center">Ativos</th><th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($categorias as $c) : ?>
                <tr>
                  <td><code class="fw-semibold"><?= e($c['prefixo']) ?></code></td>
                  <td><?= e($c['nome']) ?></td>
                  <td class="text-center">
                    <?php if ((int) $c['total_ativos'] > 0) : ?>
                      <a class="text-decoration-none" href="<?= url('/ativos?categoria=' . (int) $c['id']) ?>">
                        <?= (int) $c['total_ativos'] ?>
                      </a>
                    <?php else : ?>
                      <span class="text-body-secondary">0</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end text-nowrap">
                    <button class="btn btn-sm btn-outline-secondary btn-editar"
                            data-id="<?= (int) $c['id'] ?>"
                            data-nome="<?= e($c['nome']) ?>"
                            data-prefixo="<?= e($c['prefixo']) ?>"
                            title="Editar">
                      <i class="bi bi-pencil"></i>
                    </button>

                    <?php if (Auth::ehPapel('admin')) : ?>
                      <form method="post" action="<?= url('/categorias/' . (int) $c['id'] . '/excluir') ?>"
                            class="d-inline"
                            onsubmit="return confirm('Excluir a categoria <?= e($c['nome']) ?>?')">
                        <?= Csrf::campo() ?>
                        <button class="btn btn-sm btn-outline-danger"
                                <?= (int) $c['total_ativos'] > 0 ? 'disabled title="Tem ativos vinculados"' : 'title="Excluir"' ?>>
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
  var form     = document.getElementById('formCategoria');
  var titulo   = document.getElementById('tituloForm');
  var campoId  = document.getElementById('categoriaId');
  var cancelar = document.getElementById('btnCancelar');

  document.querySelectorAll('.btn-editar').forEach(function (b) {
    b.addEventListener('click', function () {
      campoId.value = b.dataset.id;
      form.nome.value = b.dataset.nome;
      form.prefixo.value = b.dataset.prefixo;
      titulo.textContent = 'Editar categoria';
      cancelar.classList.remove('d-none');
      form.nome.focus();
    });
  });

  cancelar.addEventListener('click', function () {
    form.reset();
    campoId.value = '';
    titulo.textContent = 'Nova categoria';
    cancelar.classList.add('d-none');
  });
})();
</script>
