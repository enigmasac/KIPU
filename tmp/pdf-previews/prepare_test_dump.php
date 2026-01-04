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
    <div class="col-100"><div class="text">Hola</div></div>
  </div>
</div>
</body>
</html>
HTML;

$html = prepare_pdf_html($html);

file_put_contents(__DIR__ . '/prepare_test_dump.html', $html);

echo "saved\n";
