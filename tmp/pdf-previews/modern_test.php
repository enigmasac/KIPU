<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$html = <<<HTML
<!doctype html>
<html>
<head>
<link rel="stylesheet" href="http://127.0.0.1:8000/css/print.css" type="text/css">
</head>
<body>
<div class="print-template">
  <div class="row">
    <div class="col-100">
      <div class="text text-dark">
        <h3>Factura</h3>
      </div>
    </div>
  </div>

  <div class="row modern-head pt-2 pb-2 mt-1 bg-#55588b text-white" style="background-color:#55588b !important; -webkit-print-color-adjust: exact;">
    <div class="col-58">
      <div class="text text-white p-modern">
        <img class="radius-circle" src="http://127.0.0.1:8000/img/akaunting-logo-purple.svg" alt="Logo"/>
      </div>
    </div>
    <div class="col-42">
      <div class="text text-white p-modern right-column">
        <p class="text-normal font-semibold">Enigma Developers SAC</p>
        <p class="text-white">Av. 28 de Julio 625</p>
        <p class="text-white"><span class="font-semibold">RUC:</span> 20605632875</p>
      </div>
    </div>
  </div>

  <div class="row top-spacing">
    <div class="col-50">
      <div class="text p-modern">
        <p class="font-semibold mb-0">Facturar a</p>
        <p>Superintendencia Nacional de Aduanas</p>
      </div>
    </div>
    <div class="col-50">
      <div class="text p-modern right-column">
        <p class="mb-0"><span class="font-semibold spacing w-numbers">Numero:</span> <span class="float-right spacing">F00100000019</span></p>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-100">
      <div class="text extra-spacing">
        <table class="lines lines-radius-border">
          <thead style="background-color:#55588b !important; -webkit-print-color-adjust: exact;">
            <tr>
              <td class="item text font-semibold text-alignment-left text-left text-white">Items</td>
              <td class="quantity text font-semibold text-alignment-right text-right text-white">Cantidad</td>
              <td class="price text font-semibold text-alignment-right text-right text-white">Precio</td>
              <td class="discount text font-semibold text-alignment-right text-right text-white">Descuento</td>
              <td class="total text font-semibold text-white text-alignment-right text-right">Importe</td>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="item text">Servicio</td>
              <td class="quantity text">1</td>
              <td class="price text">S/120.00</td>
              <td class="discount text">S/0.00</td>
              <td class="total text">S/120.00</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>
HTML;

$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
$html = prepare_pdf_html($html);

$pdf = app('dompdf.wrapper');
$pdf->setOptions([
    'isHtml5ParserEnabled' => true,
    'isRemoteEnabled' => true,
    'defaultFont' => 'DejaVu Sans'
]);
$pdf->loadHTML($html);
$pdf->save(__DIR__ . '/modern_test.pdf');

echo "saved\n";
