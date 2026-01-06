<?php

use App\Models\Common\Item;
use Modules\Inventory\Models\Adjustment;
use Modules\Inventory\Models\ItemGroup;
use Modules\Inventory\Models\TransferOrder;
use Modules\Inventory\Models\Variant;
use Modules\Inventory\Models\Warehouse;

return [
    Item::class => [
        [
            'location' => [
                'code' => 'inventory-items',
                'name' => 'inventory::general.inventory_items',
            ],
            'sort_orders' => [
                'type' => ['general.types', 1],
                'name' => 'general.name',
                'item' => ['general.categories', 1],
                'picture' => ['general.pictures', 1],
                'description' => 'general.description',
                'sale_information' => 'items.sale_information',
                'purchase_information' => 'items.purchase_information',
                'sale_price' => 'items.sale_price',
                'purchase_price' => 'items.purchase_price',
                'tax_ids' => ['general.taxes', 1],
                'returnable' => 'inventory::general.returnable',
                'track_inventory' => 'inventory::items.track_inventory',
                'add_variants' => 'inventory::items.add_variants',
                'sku' => 'inventory::general.sku',
                'unit' => 'inventory::general.unit',
                'barcode' => 'inventory::general.barcode',
            ],
            'views' => [
                'crud' => [
                    'inventory::items.create',
                    'inventory::items.edit'
                ],
                'show' => [
                    'inventory::items.show'
                ],
            ],
        ],
        'export' => 'Modules\Inventory\Exports\Items\Sheets\Items',
    ],
    ItemGroup::class => [
        [
            'location' => [
                'code' => 'inventory-item-groups',
                'name' => 'inventory::general.item_groups',
            ],
            'sort_orders' => [
                'name' => 'general.name',
                'item' => ['general.categories', 1],
                'picture' => ['general.pictures', 1],
                'description' => 'general.description',
                'tax_ids' => ['general.taxes', 1],
                'unit' => 'inventory::general.unit',
            ],
            'views' => [
                'crud' => [
                    'inventory::item-groups.create',
                    'inventory::item-groups.edit'
                ],
                'show' => [
                ],
            ],
        ],
        'export' => [
            'Modules\Inventory\Exports\ItemGroups\Sheets\ItemGroups',
            'Modules\Inventory\Exports\ItemGroups\Sheets\ItemGroupVariants',
            'Modules\Inventory\Exports\ItemGroups\Sheets\ItemGroupVariantValues',
            'Modules\Inventory\Exports\ItemGroups\Sheets\ItemGroupItems',
        ],
    ],
    Variant::class => [
        [
            'location' => [
                'code' => 'inventory-variants',
                'name' => 'inventory::general.variants',
            ],
            'sort_orders' => [
                'name' => 'general.name',
            ],
            'views' => [
                'crud' => [
                    'inventory::variants.create',
                    'inventory::variants.edit'
                ],
                'show' => [
                ],
            ],
        ],
        'export' => [
            'Modules\Inventory\Exports\Variants\Sheets\Variants',
            'Modules\Inventory\Exports\Variants\Sheets\VariantValues'
        ],
    ],
    TransferOrder::class => [
        [
            'location' => [
                'code' => 'inventory-transfer-orders',
                'name' => 'inventory::general.transfer_orders',
            ],
            'sort_orders' => [
                'transfer_order' => ['inventory::general.transfer_orders', 1],
                'transfer_order_number' => ['inventory::transferorders.transfer_order_number', 1],
                'date' => 'general.date',
                'reason' => 'inventory::transferorders.reason'
            ],
            'views' => [
                'crud' => [
                    'inventory::transfer-orders.create',
                    'inventory::transfer-orders.edit'
                ],
                'show' => [
                    'inventory::transfer-orders.show'
                ],
            ],
        ],
    ],
    Adjustment::class => [
        [
            'location' => [
                'code' => 'inventory-adjustments',
                'name' => 'inventory::general.adjustments',
            ],
            'sort_orders' => [
                'adjustment_number' => 'inventory::adjustments.adjustment_number',
                'date' => 'general.date',
                'reason' => 'inventory::transferorders.reason',
                'description' => 'general.description'
            ],
            'views' => [
                'crud' => [
                    'inventory::adjustments.create',
                    'inventory::adjustments.edit'
                ],
                'show' => [
                    'inventory::adjustments.show'
                ],
            ],
        ],
    ],
    Warehouse::class => [
        [
            'location' => [
                'code' => 'inventory-warehouses',
                'name' => 'inventory::general.warehouses',
            ],
            'sort_orders' => [
                'name' => 'general.name',
                'email' => 'general.email',
                'phone' => 'general.phone',
                'address' => 'general.address',
                'city' => ['general.cities', 1],
                'zip_code' => 'general.zip_code',
                'state' => 'general.state',
                'country' => ['general.countries', 1],
                'description' => 'general.description',
                'default_warehouse' => 'inventory::general.default_warehouse'
            ],
            'views' => [
                'crud' => [
                    'inventory::warehouses.create',
                    'inventory::warehouses.edit'
                ],
                'show' => [
                    'inventory::warehouses.show'
                ],
            ],
        ],
        'export' => 'Modules\Inventory\Exports\Warehouses\Warehouses',
    ],
];
