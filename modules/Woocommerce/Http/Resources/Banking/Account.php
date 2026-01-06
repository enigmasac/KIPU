<?php

namespace Modules\Woocommerce\Http\Resources\Banking;

use Illuminate\Http\Resources\Json\JsonResource;

class Account extends JsonResource
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
            'name'            => $this->method_title,
            'number'          => $this->id,
            'currency_code'   => setting('default.currency'),
            'opening_balance' => 0,
            'enabled'         => true,
            'company_id'      => company_id(),
            'created_from'    => source_name('woocommerce'),
        ];
    }
}
