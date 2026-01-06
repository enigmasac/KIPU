<?php

namespace Modules\Inventory\Http\Resources;

use App\Http\Resources\Common\ItemTax;
use App\Http\Resources\Setting\Category;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Inventory\Http\Resources\InventoryItem;

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
        $inventoryItem = $this->inventory()->first();

        if ($inventoryItem) {
            $sku = $inventoryItem->sku;
            $track_inventory = $inventoryItem->track_inventory;
            $unit = $inventoryItem->unit;
        }

        $total_stock = $this->inventory()->sum('opening_stock');

        if ($this->sale_price) {
            $sale_price_formatted = money($this->sale_price, setting('default.currency'), true)->format();
        }

        if ($this->purchase_price) {
            $purchase_price_formatted = money($this->purchase_price, setting('default.currency'), true)->format();
        }

        $picture = $this->picture;

        if ($picture) {
            $picture = $picture->getUrl();
        }

        return [
            'id'                        => $this->id,
            'company_id'                => $this->company_id,
            'type'                      => $this->type,
            'name'                      => $this->name,
            'description'               => $this->description,
            'sale_price'                => $this->sale_price,
            'sale_price_formatted'      => $sale_price_formatted ?? null,
            'purchase_price'            => $this->purchase_price,
            'purchase_price_formatted'  => $purchase_price_formatted ?? null,
            'total_stock'               => $total_stock, 
            'category_id'               => $this->category_id,
            'picture'                   => $picture,
            'enabled'                   => $this->enabled,
            'created_from'              => $this->created_from,
            'created_by'                => $this->created_by,
            'created_at'                => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at'                => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            'taxes'                     => [static::$wrap => ItemTax::collection($this->taxes)],
            'items'                     => [static::$wrap => InventoryItem::collection($this->inventory()->get())],
            'category'                  => new Category($this->category),
            'sku'                       => $sku ?? null,
            'track_inventory'           => $track_inventory ?? null,
            'unit'                      => $unit ?? NULL,
        ];
    }
}