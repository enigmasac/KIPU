<?php

return [
    'name'                      => 'Inventario',
    'description'               => 'Gestión de contabilidad e inventario bajo un mismo techo',

    'items'                     => 'Artículo|Artículos',
    'inventories'               => 'Inventario|Inventarios',
    'variants'                  => 'Variante|Variantes',
    'manufacturers'             => 'Fabricante|Fabricantes',
    'transfer_orders'           => 'Orden de transferencia|Órdenes de transferencia',
    'adjustments'               => 'Ajuste|Ajustes',
    'warehouses'                => 'Almacén|Almacenes',
    'histories'                 => 'Historial|Historiales',
    'item_groups'               => 'Grupo|Grupos',
    'barcode'                   => 'Código de barras',
    'print_barcode'             => 'Imprimir código de barras',
    'generate_barcode'          => 'Generar código de barras',
    'sku'                       => 'SKU',
    'quantity'                  => 'Cantidad',
    'add_warehouse'             => 'Añadir almacén',
    'edit_warehouse'            => 'Editar almacén',
    'default'                   => 'Predeterminado',
    'stock'                     => 'Stock',
    'information'               => 'Información',
    'default_warehouse'         => 'Almacén predeterminado',
    'track_inventory'           => 'Rastrear inventario',
    'negative_stock'            => 'Stock negativo',
    'expented_income'           => 'Ingresos esperados',
    'sale_item_quantity'        => 'Cantidad de artículos vendidos',
    'sale_item_amount'          => 'Monto de artículos vendidos',
    'purchase_item_quantity'    => 'Cantidad de artículos comprados',
    'purchase_item_amount'      => 'Monto de artículos comprados',
    'income'                    => 'Ingresos',
    'invalid_stock'             => 'Stock en almacén :stock',
    'low_stock'                 => ':name Stock bajo (:count - :warehouse)',
    'unit'                      => 'Unidad',
    'returnable'                => 'Artículo retornable',
    'overview'                  => 'Vista general',
    'action'                    => 'Acción',
    'record'                    => 'Registro',
    'required_fields'           => 'El campo :attribute es obligatorio.',
    'sort_sale_price'           => 'Precio Venta',
    'sort_purchase_price'       => 'Precio Compra',
    'inventory_items'           => 'Artículos de inventario',
    'destination_warehouse'     => 'Almacén de destino',
    'source_warehouse'          => 'Almacén de origen',

    'menu' => [
        'inventory'             => 'Inventario',
        'item_groups'           => 'Grupos',
        'variants'               => 'Variantes',
        'manufacturers'         => 'Fabricantes',
        'warehouses'            => 'Almacenes',
        'histories'             => 'Historiales',
        'reports'               => 'Reportes',
    ],

    'notifications' => [
        'reorder_level'         => 'Nivel de reorden de :count artículos',
    ],

    'document' => [
        'detail'                => 'Se utiliza un almacén :class para la contabilidad adecuada de su :type y para mantener sus informes precisos.',
    ],

    'empty' => [
        'items'                 => 'Los artículos pueden ser productos o servicios. Puede usar artículos al crear facturas y facturas de compra.',
        'adjustments'           => "Debido a algunas razones, como artículos dañados y artículos robados, etc.,
                                    las existencias reales de su empresa y las existencias registradas pueden no ser iguales.
                                    El ajuste de inventario le permite registrar los artículos faltantes.",
        'warehouses'            => 'Puede añadir y gestionar múltiples almacenes.
                                    También puede realizar un seguimiento del control de stock de todos sus artículos por almacén.
                                    La vista general y el historial del almacén le brindan información sobre las operaciones de los almacenes.',
        'transfer-orders'       => 'La orden de transferencia le permite realizar un seguimiento del movimiento de artículos de un almacén a otro.',
        'variants'              => 'Puede añadir y gestionar variantes que describan mejor sus artículos en la sección Variantes.
                                    Puede crear un grupo de artículos que tengan las mismas variantes, como color, tamaño, etc.',
        'item-groups'           => 'Utiliza los Grupos para gestionar productos que son esencialmente el mismo pero tienen variaciones, como tallas, colores o materiales. Crea un Grupo, añade las variantes y el sistema generará automáticamente los artículos. Cada variante tendrá su propio stock y código SKU para SUNAT.',
        'histories'             => "Aún no ha registrado ninguna acción de inventario. Todas las actividades de inventario de su negocio
                                    se registrarán aquí. Comience a usar la aplicación de inventario creando un artículo.",
        'title' => [
            'adjustments'       => 'Ajuste',
            'warehouses'        => 'Almacenes',
            'transfer-orders'   => 'Órdenes de transferencia',
            'variants'          => 'Variantes',
            'item-groups'       => 'Grupos de artículos',
        ]
    ],

    'reports' => [
        'name' => [
            'stock_status'      => 'Estado del stock',
            'sale_summary'      => 'Resumen de ventas',
            'purchase_summary'  => 'Resumen de compras',
            'item_summary'      => 'Resumen de artículos',
            'profit_loss'       => 'Pérdidas y Ganancias (Inventario)',
            'income_summary'    => 'Resumen de ingresos (Inventario)',
            'expense_summary'   => 'Resumen de gastos (Inventario)',
            'income_expense'    => 'Ingresos vs Gastos (Inventario)',
        ],

        'description' => [
            'stock_status'      => 'Seguimiento del stock de artículos',
            'sale_summary'      => 'Seguimiento del stock de artículos de venta',
            'purchase_summary'  => 'Seguimiento del stock de artículos de compra',
            'item_summary'      => 'La lista de la información del artículo',
            'profit_loss'       => 'Pérdidas y ganancias trimestrales por inventario.',
            'income_summary'    => 'Resumen de ingresos mensual por inventario.',
            'expense_summary'   => 'Resumen de gastos mensual por inventario.',
            'income_expense'    => 'Ingresos vs gastos mensuales por inventario.',
        ],
    ],
];
