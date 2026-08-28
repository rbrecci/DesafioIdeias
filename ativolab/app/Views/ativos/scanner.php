<div class="row justify-content-center">
  <div class="col-lg-7">

    <div class="d-flex align-items-center gap-2 mb-3">
      <a class="btn btn-sm btn-outline-secondary" href="<?= url('/ativos') ?>"><i class="bi bi-arrow-left"></i></a>
      <h1 class="h3 mb-0">Ler etiqueta</h1>
    </div>

    <div id="avisoInseguro" class="alert alert-danger d-none">
      <strong>Sem HTTPS.</strong> O navegador so libera a camera em conexao segura.
      Acesse este sistema por <code>https://</code> para usar o leitor.
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <p class="text-body-secondary small">
          Aponte a camera para a etiqueta QR do equipamento. A ficha abre sozinha assim que o codigo for lido.
        </p>

        <div id="leitor" class="rounded overflow-hidden bg-dark" style="min-height:240px"></div>

        <div id="estado" class="small text-body-secondary mt-2">Camera desligada.</div>

        <div class="d-flex gap-2 mt-3">
          <button class="btn btn-primary" type="button" id="btnLigar">
            <i class="bi bi-camera-video me-1"></i>Ligar camera
          </button>
          <button class="btn btn-outline-secondary d-none" type="button" id="btnDesligar">Desligar</button>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm mt-3">
      <div class="card-body">
        <h2 class="h6 text-uppercase text-body-secondary mb-2" style="letter-spacing:.06em">
          Sem camera por perto?
        </h2>
        <form method="get" action="<?= url('/ativos') ?>" class="d-flex gap-2">
          <input class="form-control font-monospace text-uppercase" name="busca"
                 placeholder="Digite o patrimonio" aria-label="Buscar por patrimonio">
          <button class="btn btn-outline-primary" type="submit">Buscar</button>
        </form>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function () {
  var base      = <?= json_encode(url('/p/'), JSON_UNESCAPED_SLASHES) ?>;
  var leitorEl  = document.getElementById('leitor');
  var estado    = document.getElementById('estado');
  var btnLigar  = document.getElementById('btnLigar');
  var btnParar  = document.getElementById('btnDesligar');
  var leitor    = null;
  var navegando = false;

  if (!window.isSecureContext) {
    document.getElementById('avisoInseguro').classList.remove('d-none');
    btnLigar.disabled = true;
    estado.textContent = 'Indisponivel sem HTTPS.';
    return;
  }

  if (typeof Html5Qrcode === 'undefined') {
    estado.textContent = 'Nao foi possivel carregar a biblioteca de leitura. Verifique sua conexao.';
    btnLigar.disabled = true;
    return;
  }

  function aoLer(texto) {
    if (navegando) { return; }
    navegando = true;

    if (navigator.vibrate) { navigator.vibrate(60); }
    estado.textContent = 'Lido: ' + texto;

    // A etiqueta guarda a URL completa; digitacao manual pode trazer so o patrimonio.
    var destino = /^https?:\/\//i.test(texto) ? texto : base + encodeURIComponent(texto.trim());

    parar().then(function () { location.href = destino; });
  }

  function ligar() {
    leitor = new Html5Qrcode('leitor', { verbose: false });
    estado.textContent = 'Pedindo permissao da camera...';

    leitor.start(
      { facingMode: 'environment' },
      { fps: 10, qrbox: { width: 240, height: 240 } },
      aoLer,
      function () { /* frame sem codigo: silencioso de proposito */ }
    ).then(function () {
      estado.textContent = 'Camera ativa. Aponte para a etiqueta.';
      btnLigar.classList.add('d-none');
      btnParar.classList.remove('d-none');
    }).catch(function (err) {
      var msg = {
        NotAllowedError: 'Permissao negada. Libere a camera nas configuracoes do site.',
        NotFoundError: 'Nenhuma camera encontrada neste aparelho.',
        NotReadableError: 'A camera esta ocupada por outro aplicativo.'
      }[err && err.name];

      estado.textContent = msg || ('Nao foi possivel abrir a camera: ' + err);
    });
  }

  function parar() {
    if (!leitor) { return Promise.resolve(); }

    return leitor.stop().then(function () {
      leitor.clear();
      leitor = null;
      leitorEl.innerHTML = '';
      btnLigar.classList.remove('d-none');
      btnParar.classList.add('d-none');
      estado.textContent = 'Camera desligada.';
    }).catch(function () { /* ja estava parada */ });
  }

  btnLigar.addEventListener('click', ligar);
  btnParar.addEventListener('click', parar);
  window.addEventListener('pagehide', parar);
})();
</script>
