<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$html = <<<HTML
<!doctype html>
<html>
<head>
<link rel="stylesheet" href="http://127.0.0.1:8000/css/print.css" type="text/css">
<style>body{background:#fff;}</style>
</head>
<body>
<div class="print-template">
  <div class="row">
    <div class="col-100"><div class="text">Hola</div></div>
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
$pdf->save(__DIR__ . '/prepare_test_white.pdf');

echo "saved\n";
