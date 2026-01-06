<?php

namespace Modules\Woocommerce\Http\Resources\Module;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryVariantValue extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return array_merge(
            [
                'company_id'   => company_id(),
                'name'         => html_entity_decode($this->name),
                'created_from' => source_name('woocommerce'),
            ],
            isset($this->option_id) ? ['variant_id' => $this->option_id] : []
        );
    }
}
