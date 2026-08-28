<?php

use App\Core\Config;
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titulo ?? 'Etiquetas') ?> &middot; <?= e(Config::get('app_nome', 'AtivoLab')) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
  /* Folha A4 com grade de 3 colunas x 8 linhas = 24 etiquetas por pagina.
     Medidas em milimetros: o navegador imprime na escala certa e o QR sai
     no tamanho fisico previsto, que e o que garante leitura a 15 cm. */
  :root { --etq-larg: 63mm; --etq-alt: 33mm; }

  body { background: #f2f4f7; }

  .folha {
    width: 210mm;
    min-height: 297mm;
    margin: 0 auto;
    padding: 8mm;
    background: #fff;
    box-shadow: 0 2px 14px rgba(0,0,0,.12);
  }

  .grade {
    display: grid;
    grid-template-columns: repeat(3, var(--etq-larg));
    gap: 2mm;
    justify-content: center;
  }

  .etiqueta {
    width: var(--etq-larg);
    height: var(--etq-alt);
    border: 1px dashed #c9ced6;
    border-radius: 2mm;
    padding: 2mm;
    display: flex;
    gap: 2mm;
    align-items: center;
    overflow: hidden;
    break-inside: avoid;
    page-break-inside: avoid;
  }

  .etiqueta .qr { flex: 0 0 auto; width: 26mm; height: 26mm; }
  .etiqueta .qr svg { width: 100%; height: 100%; display: block; }

  .etiqueta .txt { min-width: 0; font-family: Arial, Helvetica, sans-serif; line-height: 1.15; }
  .etiqueta .pat { font-size: 10pt; font-weight: 700; font-family: "Courier New", monospace; }
  .etiqueta .nom { font-size: 7pt; margin-top: .6mm; overflow: hidden;
                   display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
  .etiqueta .set { font-size: 6.5pt; color: #555; margin-top: .8mm; }
  .etiqueta .org { font-size: 5.5pt; color: #888; margin-top: .8mm; letter-spacing: .04em; }

  @page { size: A4 portrait; margin: 0; }

  @media print {
    body { background: #fff; }
    .nao-imprimir { display: none !important; }
    .folha { width: auto; min-height: auto; margin: 0; padding: 8mm; box-shadow: none; }
    .etiqueta { border-color: #e3e6ea; }
  }
</style>
</head>
<body>

<?= $conteudo ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
/**
 * Desenha os QR no proprio navegador, em SVG.
 *
 * Foi decisao de projeto nao gerar o QR no PHP: em hospedagem compartilhada
 * a extensao imagick costuma faltar, e SVG imprime mais nitido que PNG em
 * etiqueta pequena.
 */
(function () {
  if (typeof QRCode === 'undefined') {
    var aviso = document.getElementById('avisoQr');
    if (aviso) { aviso.classList.remove('d-none'); }
    return;
  }

  var origem = location.origin;
  var alvos  = document.querySelectorAll('.qr[data-patrimonio]');
  var falhas = 0;

  alvos.forEach(function (el) {
    var destino = origem + el.dataset.base + encodeURIComponent(el.dataset.patrimonio);

    QRCode.toString(destino, {
      type: 'svg',
      margin: 0,
      errorCorrectionLevel: 'M'
    }, function (err, svg) {
      if (err) { falhas++; return; }
      el.innerHTML = svg;
    });
  });

  var contador = document.getElementById('contador');
  if (contador) { contador.textContent = alvos.length; }
})();
</script>
</body>
</html>
