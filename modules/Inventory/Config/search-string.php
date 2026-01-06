<?php

return [

    Modules\Inventory\Models\Common\Item::class => [
        'columns' => [
            'id',
            'name' => ['searchable' => true],
            'description' => ['searchable' => true],
            'enabled' => ['boolean' => true],
            'category_id' => [
                'route' => ['categories.index', 'search=type:item enabled:1']
            ],
            'sale_price',
            'purchase_price',
            'inventory.warehouse_id' => [
                'route' => 'inventory.warehouses.index'
            ],
            'created_at' => ['date' => true],
            'updated_at' => ['date' => true],
        ],
    ],

    Modules\Inventory\Models\Adjustment::class => [
        'columns' => [
            'adjustment_number' => ['searchable' => true],
            'reason' => ['searchable' => true],
            'date' => ['date' => true],
            'warehouse_id' => [
                'route' => 'inventory.warehouses.index'
            ],
        ],
    ],

    Modules\Inventory\Models\ItemGroup::class => [
        'columns' => [
            'name' => ['searchable' => true],
            'enabled' => ['boolean' => true],
            'category_id' => [
                'route' => ['categories.index', 'search=type:item']
            ],
        ],
    ],

    Modules\Inventory\Models\Variant::class => [
        'columns' => [
            'name' => ['searchable' => true],
            'enabled' => ['boolean' => true],
        ],
    ],

    Modules\Inventory\Models\TransferOrder::class => [
        'columns' => [
            'id',
            'transfer_order' => ['searchable' => true],
            'transfer_quantity' => ['searchable' => true],
            'date' => ['date' => true],
            'source_warehouse_id' => [
                'route' => 'inventory.warehouses.index'
            ],
            'destination_warehouse_id' => [
                'route' => 'inventory.warehouses.index'
            ],
        ],
    ],

    Modules\Inventory\Models\Warehouse::class => [
        'columns' => [
            'id',
            'name' => ['searchable' => true],
            'email' => ['searchable' => true],
            'phone' => ['searchable' => true],
            'enabled' => ['boolean' => true],
        ],
    ],

    Modules\Inventory\Models\History::class => [
        'columns' => [
            'id',
            'created_at' => ['date' => true],
            'warehouse_id' => [
                'route' => 'inventory.warehouses.index'
            ],
            'item_id' => [
                'route' => 'items.index'
            ],
        ],
    ],
];
