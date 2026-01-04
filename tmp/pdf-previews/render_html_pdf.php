<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$html = file_get_contents(__DIR__ . '/prepare_test.html');
$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
$html = prepare_pdf_html($html);

$pdf = app('dompdf.wrapper');
$pdf->setOptions([
    'isHtml5ParserEnabled' => true,
    'isRemoteEnabled' => true,
    'defaultFont' => 'DejaVu Sans'
]);
$pdf->loadHTML($html);

$pdf->save(__DIR__ . '/prepare_test.pdf');

fwrite(STDOUT, "saved\n");
