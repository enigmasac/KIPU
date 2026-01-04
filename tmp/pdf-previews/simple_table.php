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
  <table class="lines">
    <thead style="background-color:#3c3f72 !important; -webkit-print-color-adjust: exact;">
      <tr>
        <td class="item text font-semibold text-white">Items</td>
        <td class="quantity text font-semibold text-white">Cantidad</td>
        <td class="price text font-semibold text-white">Precio</td>
        <td class="discount text font-semibold text-white">Descuento</td>
        <td class="total text font-semibold text-white">Importe</td>
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
$pdf->save(__DIR__ . '/simple_table.pdf');

echo "saved\n";
