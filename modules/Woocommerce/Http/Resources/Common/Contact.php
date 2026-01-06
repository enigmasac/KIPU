<?php

namespace Modules\Woocommerce\Http\Resources\Common;

use Illuminate\Support\Str;
use Illuminate\Http\Resources\Json\JsonResource;

class Contact extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if (isset($this->address)) {
            return [
                'company_id' => company_id(),
                'type'       => 'customer',
                'name'       => false === empty($this->name) ? Str::limit($this->name, 150) : $this->email,
                'email'      => $this->email,
                'enabled'    => true,
                'phone'      => Str::limit($this->phone, 150),
                'address'    => $this->address,
            ];
        }

        $name = Str::limit(collect([$this->first_name, $this->last_name])->filter()->implode(' '), 150);
        if (empty($this->first_name) && empty($this->last_name)) {
            $name = $this->email;
        }

        return [
            'company_id'   => company_id(),
            'type'         => 'customer',
            'name'         => $name,
            'email'        => $this->email,
            'enabled'      => true,
            'phone'        => Str::limit($this->billing->phone, 150),
            'address'      => $this->getAddress($this->billing),
            'created_from' => source_name('woocommerce'),
        ];
    }

    public function getAddress($value)
    {
        return collect(
            [
                $value->address_1,
                $value->address_2,
                $value->city,
                $value->postcode,
                $value->country,
            ]
        )->filter()->implode("\r\n");
    }
}
