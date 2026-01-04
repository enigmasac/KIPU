<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$doc = App\Models\Document\Document::where('document_number', 'F00100000019')->first();

if (! $doc) {
    fwrite(STDERR, "not found\n");
    exit(1);
}

$html = view($doc->template_path, [
    'invoice' => $doc,
    'document' => $doc,
    'currency_style' => true,
])->render();

$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
$html = prepare_pdf_html($html);

file_put_contents(__DIR__ . '/invoice.html', $html);

fwrite(STDOUT, "ok\n");
