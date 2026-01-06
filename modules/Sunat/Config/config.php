<?php

return [
    'name' => 'Sunat',
    'environment' => env('SUNAT_ENVIRONMENT', 'beta'),
    'auto_emit' => env('SUNAT_AUTO_EMIT', true),
    'max_retries' => env('SUNAT_MAX_RETRIES', 3),
    'timeout' => env('SUNAT_TIMEOUT', 30),
    'storage_disk' => env('SUNAT_STORAGE_DISK', 'local'),
    'storage_path' => env('SUNAT_STORAGE_PATH', 'sunat'),

    'credit_note_reason_codes' => [
        '01' => 'Anulación de la operación',
        '02' => 'Anulación por error en el RUC',
        '03' => 'Corrección por error en la descripción',
        '04' => 'Descuento global',
        '05' => 'Descuento por ítem',
        '06' => 'Devolución total',
        '07' => 'Devolución por ítem',
        '08' => 'Bonificación',
        '09' => 'Disminución en el valor',
        '10' => 'Otros Conceptos',
    ],

    'debit_note_reason_codes' => [
        '01' => 'Intereses por mora',
        '02' => 'Aumento en el valor',
        '03' => 'Penalidades/ otros conceptos',
    ],
];
