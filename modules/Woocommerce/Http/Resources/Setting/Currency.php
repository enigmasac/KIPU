<?php

namespace Modules\Woocommerce\Http\Resources\Setting;

use Illuminate\Http\Resources\Json\JsonResource;

class Currency extends JsonResource
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
            'company_id'       => company_id(),
            'name'             => $this->currency,
            'code'             => $this->currency,
            'rate'             => 1,
            'default_currency' => false,
            'enabled'          => true,
            'created_from'     => source_name('woocommerce'),
        ];
    }
}
