<?php

namespace Modules\Woocommerce\Http\Resources\Common;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemStock extends JsonResource
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
            'company_id'   => company_id(),
            'sku'          => $this->sku,
            'item_id'      => $this->id,
            'quantity'     => $this->quantity,
            'created_from' => source_name('woocommerce'),
        ];
    }
}
