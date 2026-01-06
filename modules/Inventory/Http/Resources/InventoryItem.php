<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryItem extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'company_id'        => $this->company_id,
            'created_by'        => $this->created_by,
            'item_id'           => $this->item_id,
            'warehouse_id'      => $this->warehouse_id,
            'opening_stock'     => $this->opening_stock,
            'reorder_level'     => $this->reorder_level,
            'default_warehouse' => $this->default_warehouse,
        ];
    }
}
