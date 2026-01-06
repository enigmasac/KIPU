<?php

namespace Modules\Woocommerce\Http\Resources\Setting;

use Illuminate\Http\Resources\Json\JsonResource;

class Tax extends JsonResource
{
    public const WOOCOMMERCE_FIXED_TAX_RATE_TYPE      = 'reduced-rate';
    public const WOOCOMMERCE_PERCENTAGE_TAX_RATE_TYPE = 'standard';
    public const WOOCOMMERCE_ZERO_TAX_RATE_TYPE       = 'zero_rate';

    //shipping_lines
    public const WOOCOMMERCE_FLAT_RATE     = 'flat_rate';
    public const WOOCOMMERCE_FREE_SHIPPING = 'free_shipping';
    public const WOOCOMMERCE_LOCAL_PICKUP  = 'local_pickup';
    public const WOOCOMMERCE_OTHER         = 'other';

    public const FIXED_TAX_TYPE      = 'fixed';
    public const PERCENTAGE_TAX_TYPE = 'normal';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        switch ($this->class) {
            case self::WOOCOMMERCE_FIXED_TAX_RATE_TYPE:
                $type = self::FIXED_TAX_TYPE;
                break;
            case self::WOOCOMMERCE_ZERO_TAX_RATE_TYPE:
                $type = self::FIXED_TAX_TYPE;
                break;
            case self::WOOCOMMERCE_PERCENTAGE_TAX_RATE_TYPE:
            default:
                $type = self::PERCENTAGE_TAX_TYPE;
                break;
        }

        return [
            'company_id'   => company_id(),
            'name'         => $this->name,
            'type'         => $type,
            'rate'         => $this->rate,
            'enabled'      => true,
            'created_from' => source_name('woocommerce'),
        ];
    }
}
