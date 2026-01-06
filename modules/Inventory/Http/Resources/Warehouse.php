<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Inventory\Http\Resources\InventoryItem;

class Warehouse extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if (setting('inventory.default_warehouse') == $this->id) {
            $default = true;
        } else {
            $default = false;
        }

        return [
            'id'                => $this->id,
            'company_id'        => $this->company_id,
            'name'              => $this->name,
            'email'             => $this->email,
            'address'           => $this->address,
            'city'              => $this->city,
            'state'             => $this->state,
            'zip_code'          => $this->zip_code,
            'country'           => $this->country,
            'description'       => $this->description,
            'enabled'           => $this->enabled,
            'default_warehouse' => $default,
            'created_from'      => $this->created_from,
            'created_by'        => $this->created_by,
            'created_at'        => $this->created_at ? $this->created_at->toIso8601String() : '',
            'updated_at'        => $this->updated_at ? $this->updated_at->toIso8601String() : '',
            'items'             => [static::$wrap => InventoryItem::collection($this->items)],
        ];
    }
}
