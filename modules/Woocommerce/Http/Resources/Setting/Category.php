<?php

namespace Modules\Woocommerce\Http\Resources\Setting;

use Illuminate\Http\Resources\Json\JsonResource;

class Category extends JsonResource
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
            'type'         => 'item',
            'name'         => $this->name,
            'enabled'      => true,
            'created_from' => source_name('woocommerce'),
        ];
    }
}
