<?php use App\Core\Csrf; ?>
<div class="text-center mb-4">
  <i class="bi bi-box-seam display-5 text-primary"></i>
  <h1 class="h3 mt-2 mb-1">AtivoLab</h1>
  <p class="text-body-secondary small mb-0">Gestao de laboratorios e ativos</p>
</div>

<div class="card shadow-sm border-0">
  <div class="card-body p-4">
    <form method="post" action="<?= url('/login') ?>" autocomplete="on">
      <?= Csrf::campo() ?>

      <div class="mb-3">
        <label class="form-label" for="email">E-mail</label>
        <input class="form-control" type="email" id="email" name="email"
               value="<?= velho('email') ?>" required autofocus autocomplete="username">
      </div>

      <div class="mb-3">
        <label class="form-label" for="senha">Senha</label>
        <input class="form-control" type="password" id="senha" name="senha"
               required autocomplete="current-password">
      </div>

      <button class="btn btn-primary w-100" type="submit">
        <i class="bi bi-box-arrow-in-right me-1"></i>Entrar
      </button>
    </form>
  </div>
</div>
