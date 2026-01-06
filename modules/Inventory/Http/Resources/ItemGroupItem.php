<?php

namespace Modules\Inventory\Http\Resources;

use App\Http\Resources\Common\ItemTax;
use App\Http\Resources\Setting\Category;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Inventory\Http\Resources\InventoryItem;

class ItemGroupItem extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {   
        $inventoryItem = $this->item->inventory()->first();

        if ($inventoryItem) {
            $sku = $inventoryItem->sku;
            $track_inventory = $inventoryItem->track_inventory;
            $unit = $inventoryItem->unit;
        }

        $total_stock = $this->item->inventory()->sum('opening_stock');

        if ($this->item->sale_price) {
            $sale_price_formatted = money($this->item->sale_price, setting('default.currency'), true)->format();
        }

        if ($this->item->purchase_price) {
            $purchase_price_formatted = money($this->item->purchase_price, setting('default.currency'), true)->format();
        }

        $picture = $this->item->picture;

        if ($picture) {
            $picture = $picture->getUrl();
        }

        return [
            'id'                        => $this->item->id,
            'company_id'                => $this->company_id,
            'type'                      => $this->item->type,
            'name'                      => $this->item->name,
            'description'               => $this->item->description,
            'sale_price'                => $this->item->sale_price,
            'sale_price_formatted'      => $sale_price_formatted ?? null,
            'purchase_price'            => $this->item->purchase_price,
            'purchase_price_formatted'  => $purchase_price_formatted ?? null,
            'total_stock'               => $total_stock, 
            'category_id'               => $this->item->category_id,
            'picture'                   => $picture,
            'enabled'                   => $this->item->enabled,
            'created_from'              => $this->item->created_from,
            'created_by'                => $this->item->created_by,
            'created_at'                => $this->item->created_at ? $this->item->created_at->toIso8601String() : null,
            'updated_at'                => $this->item->updated_at ? $this->item->updated_at->toIso8601String() : null,
            'taxes'                     => [static::$wrap => ItemTax::collection($this->item->taxes)],
            'items'                     => [static::$wrap => InventoryItem::collection($this->item->inventory()->get())],
            'category'                  => new Category($this->item->category),
            'sku'                       => $sku ?? null,
            'track_inventory'           => $track_inventory ?? null,
            'unit'                      => $unit ?? NULL,
        ];
    }
}
