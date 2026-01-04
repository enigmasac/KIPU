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
  <div class="row border-bottom-1">
    <div class="col-58">
      <div class="text">
        <p>Left content line 1</p>
        <p>Left content line 2</p>
      </div>
    </div>
    <div class="col-42">
      <div class="text right-column">
        <p>Right content line 1</p>
        <p>Right content line 2</p>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-100">
      <div class="text">After row</div>
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
$pdf->save(__DIR__ . '/column_test.pdf');

echo "saved\n";
