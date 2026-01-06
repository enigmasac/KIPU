<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class History extends JsonResource
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
            'id'            => $this->id,
            'company_id'    => $this->company_id,
            'user_id'       => $this->user_id,
            'item_id'       => $this->item_id,
            'warehouse_id'  => $this->warehouse_id,
            'type_type'     => $this->type_type,
            'type_id'       => $this->type_id,
            'quantity'      => $this->quantity,
            'created_from'  => $this->created_from,
            'created_by'    => $this->created_by,
            'created_at'    => $this->created_at ? $this->created_at->toIso8601String() : '',
            'updated_at'    => $this->updated_at ? $this->updated_at->toIso8601String() : '',
        ];
    }
}
