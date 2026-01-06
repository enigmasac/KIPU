<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransferOrder extends JsonResource
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
            'id'                        => $this->id,
            'company_id'                => $this->company_id,
            'transfer_order'            => $this->transfer_order,
            'transfer_order_number'     => $this->transfer_order_number,
            'date'                      => $this->date,
            'source_warehouse_id'       => $this->source_warehouse_id,
            'source_warehouse_name'     => $this->source_warehouse->name,
            'destination_warehouse_id'  => $this->destination_warehouse_id,
            'destination_warehouse_name'=> $this->destination_warehouse->name,
            'status'                    => $this->status,
            'created_from'              => $this->created_from,
            'created_by'                => $this->created_by,
            'created_at'                => $this->created_at ? $this->created_at->toIso8601String() : '',
            'updated_at'                => $this->updated_at ? $this->updated_at->toIso8601String() : '',
            'items'                     => [static::$wrap => TransferOrderItem::collection($this->transfer_order_items)],
        ];
    }
}
