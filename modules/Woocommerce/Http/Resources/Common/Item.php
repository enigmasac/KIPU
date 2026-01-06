<?php

namespace Modules\Woocommerce\Http\Resources\Common;

use App\Models\Setting\Category;
use Modules\Woocommerce\Models\WooCommerceIntegration;
use Illuminate\Http\Resources\Json\JsonResource;

class Item extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
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


        $status = true;

        switch ($this->status) {
            case 'private':
                $status = false;
                break;
        }

        return [
            'company_id'      => company_id(),
            'name'            => html_entity_decode($this->name),
            'description'     => html_entity_decode($this->description),
            'sale_price'      => empty($this->price) ? 0 : $this->price,
            'purchase_price'  => empty($this->price) ? 0 : $this->price,
            'category_id'     => $categoryIntegration->item_id ?? null,
            'enabled'         => $status,
            'created_from'    => source_name('woocommerce'),

            // Inventory
            'track_inventory' => 1,
            'unit' => 'units',
            'items'           => [
                [
                    'default_warehouse'   => 1,
                    'opening_stock'       => $this->stock_quantity ?? 1,
                    'opening_stock_value' => null,
                    'reorder_level'       => null,
                    'warehouse_id'        => setting('inventory.default_warehouse'),
                ],
            ],
            'sku'             => empty($this->sku) ? html_entity_decode($this->name) : $this->sku,
        ];
    }
}
