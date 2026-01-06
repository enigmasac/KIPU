<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Inventory\Http\Resources\ItemGroupVariantValue;

class ItemGroupVariant extends JsonResource
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
            'variant_id'        => $this->variant->id,
            'variant_name'      => $this->variant->name,
            'variant_values'    => [static::$wrap => ItemGroupVariantValue::collection($this->variant_values)]
        ];
    }
}
