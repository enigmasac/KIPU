<?php

return [
    'name'                          => 'Artículos Compuestos',
    'description'                   => 'Utilice los Artículos Compuestos para crear kits, combos o productos ensamblados. Al vender un combo, el sistema descuenta automáticamente el stock de sus componentes. Cada artículo tiene su propio SKU y Unidad de Medida para SUNAT.',

    'composite_items'               => 'Artículo Compuesto|Artículos Compuestos',
    'quantity'                      => 'Cantidad',
    'estimate_stock'                => 'Stock estimado',

    'reports' => [
        'name' => [
            'sale_summary' => 'Resumen de ventas',
            'invoice_amount' => 'Monto de la factura',
        ],
        'description' => [
            'sale_summary' => '',
            'invoice_amount' => 'Informa de los importes de las facturas de los vendedores',
        ],
    ],

    'empty' => [
        'composite_items' => 'Utilice los Artículos Compuestos para crear kits, combos o productos ensamblados. Al vender un combo, el sistema descuenta automáticamente el stock de sus componentes. Cada artículo tiene su propio SKU y Unidad de Medida para SUNAT.',
    ]
];