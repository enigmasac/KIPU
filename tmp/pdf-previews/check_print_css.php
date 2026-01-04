<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$path = public_path('css/print.css');
$ok = is_readable($path);
$size = $ok ? filesize($path) : 0;
echo "css? " . ($ok ? 'yes' : 'no') . " size=" . $size . "\n";
