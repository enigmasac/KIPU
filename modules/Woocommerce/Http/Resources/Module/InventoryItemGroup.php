<?php

namespace Modules\Woocommerce\Http\Resources\Module;

use App\Models\Setting\Category;
use Illuminate\Support\Str;
use Modules\Inventory\Models\ItemGroupItem;
use Modules\Inventory\Models\Variant;
use Modules\Woocommerce\Models\WooCommerceIntegration;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryItemGroup extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $status = true;

        switch ($this->status) {
            case 'private':
                $status = false;
                break;
        }

        if (empty($this->categories)) {
            $categoryIntegration = null;
        } else {
            $categoryIntegration = WooCommerceIntegration::where(
                [
                    'company_id'     => company_id(),
                    'woocommerce_id' => $this->categories[0]->id,
                    'item_type'      => Category::class,
                ]
            )->first();
        }

        $items          = [];
        $optionValueIds = [];
        $optionIds      = [];

        foreach ($this->akaunting_items as $akauntingItem) {
            $itemStatus = true;
            $itemNames  = [];

            switch ($akauntingItem->status) {
                case 'private':
                    $itemStatus = false;
                    break;
            }

            foreach ($akauntingItem->attributes as $attribute) {
                // Custom product attribute
                if ($attribute->id === 0) {
                    $option      = Variant::where('name', html_entity_decode($attribute->name))
                                          ->with('values')
                                          ->first();
                    $optionValue = $option->values->where('name', html_entity_decode($attribute->option))->first();

                    $optionIntegration          = new \stdClass();
                    $optionIntegration->item_id = $option->id;
                } else {
                    $optionIntegration = WooCommerceIntegration::where(
                        [
                            'company_id'     => company_id(),
                            'woocommerce_id' => $attribute->id,
                            'item_type'      => Variant::class,
                        ]
                    )->first();

                    $optionValue = $optionIntegration->item->values
                        ->where('name', html_entity_decode($attribute->option))
                        ->first();
                }


                $itemNames[]      = $optionValue->name;
                $optionValueIds[] = $optionValue->id;

                if (false === isset($optionIds[$optionIntegration->item_id])) {
                    $optionIds[$optionIntegration->item_id] =
                        ['variant_id' => $optionIntegration->item_id, 'variant_values' => []];
                }
            }

            array_unshift($itemNames, $this->name);
            $itemName = html_entity_decode(implode(' - ', $itemNames));

            $integration = WooCommerceIntegration::where(
                [
                    'company_id'     => company_id(),
                    'woocommerce_id' => $akauntingItem->id,
                    'item_type'      => ItemGroupItem::class,
                ]
            )->first();

            $item_id = null;
            if (null !== $integration) {
                $item_id = $integration->item->item_id;
            }

            $items[] = array_merge(
                [
                    'wc_variation_id'     => $akauntingItem->id,
                    'variant_value_id'    => array_unique($optionValueIds),
                    'name'                => $itemName,
                    'description'         => html_entity_decode($akauntingItem->description),
                    'sale_price'          => $akauntingItem->price,
                    'purchase_price'      => $akauntingItem->price,
                    'enabled'             => $itemStatus,
                    'opening_stock'       => $akauntingItem->stock_quantity ?? 1,
                    'default_warehouse'   => 1,
                    'opening_stock_value' => null,
                    'reorder_level'       => null,
                    'warehouse_id'        => setting('inventory.default_warehouse'),
                    'track_inventory'     => 1,
                    'items'               => [
                        [
                            'default_warehouse'   => 1,
                            'opening_stock'       => $product->stock_quantity ?? 1,
                            'opening_stock_value' => null,
                            'reorder_level'       => null,
                            'warehouse_id'        => setting('inventory.default_warehouse'),
                        ],
                    ],
                    'sku'                 => empty($akauntingItem->sku) ? Str::slug($itemName) : $akauntingItem->sku,
                ],
                (null !== $item_id) ? ['item_id' => $item_id] : []
            );
        }

        return [
            'company_id'     => company_id(),
            'name'           => html_entity_decode($this->name),
            'description'    => html_entity_decode($this->description),
            'category_id'    => $categoryIntegration->item_id ?? null,
            'variant_id'     => $optionIntegration->item_id ?? null,
            'variants'       => $optionIds,
            'variant_values' => array_unique($optionValueIds),
            'items'          => $items,
            'enabled'        => $status,
            'created_from'   => source_name('woocommerce'),
        ];
    }
}
