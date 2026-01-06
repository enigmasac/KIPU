<?php

namespace Modules\Inventory\Jobs\ItemGroups;

use App\Abstracts\Job;
use Modules\Inventory\Jobs\ItemGroups\CreateItemGroup;

class DuplicateItemGroup extends Job
{
    /**
     * Create a new job instance.
     *
     * @param  $request
     * @param  $group_item
     */
    public function __construct($group_item)
    {
        $this->group_item = $group_item;
    }

    public function handle()
    {
        \DB::transaction(function () {
            $variants = $variant_value_ids = [];
            foreach ($this->group_item->variants as $key => $variant) {
                $variants[$key] = [
                    'variant_id' => $variant->variant_id,
                    'variant_values' => $variant->variant_values->pluck('id')->toArray(),
                ];

                foreach ($variant->variant_values->pluck('id')->toArray() as $value) {
                    $variant_value_ids[] = $value;
                }
            }

            $items = [];
            foreach ($this->group_item->items as $key => $group_item) {
                $item = $group_item->item;
                $inventory_item = $group_item->item->inventory()->first();

                $items[$key] = [
                    'name'                  => $item->name,
                    'sku'                   => $inventory_item->sku . '(duplicate)',
                    'barcode'               => '',
                    'variant_value_id'      => $variant_value_ids,
                    'variant_id'            => $variants,
                    'opening_stock'         => $inventory_item->opening_stock,
                    'opening_stock_value'   => $inventory_item->opening_stock_value,
                    'sale_price'            => $item->sale_price,
                    'purchase_price'        => $item->purchase_price,
                    'reorder_level'         => $inventory_item->reorder_level ?? null,
                    'unit'                  => $inventory_item->unit,
                    'created_from'          => 'inventory::duplicate',
                    'warehouse_id'          => $inventory_item->warehouse_id,
                ];
            }

            $group_request = [
                'company_id'    => company_id(),
                'name'          => $this->group_item->name,
                'category_id'   => $this->group_item->category_id,
                'variants'      => $variants,
                'enabled'       => 1 ,
                'items'         => $items,
                'created_from'  => 'inventory::duplicate',
            ];

            $this->dispatch(new CreateItemGroup($group_request));
        });

        return true;
    }
}
