<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Inventory\Http\Resources\AdjustmentItem;

class Adjustment extends JsonResource
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
            'adjustment_number' => $this->adjustment_number,
            'date'              => $this->date,
            'description'       => $this->description,
            'reason'            => $this->reason,
            'warehouse_id'      => $this->warehouse_id,
            'warehouse_name'    => $this->warehouse->name,
            'items'             => [static::$wrap => AdjustmentItem::collection($this->adjustment_items)],
            'created_from'      => $this->created_from,
            'created_by'        => $this->created_by,
            'created_at'        => $this->created_at ? $this->created_at->toIso8601String() : '',
            'updated_at'        => $this->updated_at ? $this->updated_at->toIso8601String() : '',
        ];
    }
}
