<?php

namespace Modules\Woocommerce\Http\Resources\Module;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryVariant extends JsonResource
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
            'name'         => html_entity_decode($this->name),
            'type'         => 'select',
            'enabled'      => true,
            'items'        => $this->items ?? [],
            'created_from' => source_name('woocommerce'),
        ];
    }
}
