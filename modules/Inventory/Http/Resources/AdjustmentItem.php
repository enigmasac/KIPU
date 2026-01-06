<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdjustmentItem extends JsonResource
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
            'adjustment_id'     => $this->adjustment_id,
            'item_id'           => $this->item_id,
            'item_quantity'     => $this->item_quantity,
            'adjusted_quantity' => $this->adjusted_quantity,
            'created_from'      => $this->created_from,
            'created_by'        => $this->created_by,
            'created_at'        => $this->created_at ? $this->created_at->toIso8601String() : '',
            'updated_at'        => $this->updated_at ? $this->updated_at->toIso8601String() : '',
        ];
    }
}
