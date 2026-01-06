<?php

namespace Modules\Woocommerce\Http\Resources\Module;

use Modules\CustomFields\Models\Field;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomFields extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [];

        $customFieldMapping = collect(json_decode(setting('woocommerce.field_mapping', '')));

        if (0 === $customFieldMapping->count()) {
            return $data;
        }

        foreach ($this->resource as $metaDatum) {
            if (false === $customFieldMapping->contains('wp_field', $metaDatum->key)) {
                continue;
            }

            $customFieldData = $customFieldMapping->where('wp_field', $metaDatum->key)->first();

            $customField = Field::find($customFieldData->field_id);

            if (null !== $customField) {
                $data[$customField->code] = $metaDatum->value;
            }
        }

        return $data;
    }
}
