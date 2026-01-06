<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Inventory\Http\Resources\VariantValue;

class Variant extends JsonResource
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
            'id'                        => $this->id,
            'company_id'                => $this->company_id,
            'name'                      => $this->name,
            'enabled'                   => $this->enabled,
            'created_from'              => $this->created_from,
            'created_by'                => $this->created_by,
            'created_at'                => $this->created_at ? $this->created_at->toIso8601String() : '',
            'updated_at'                => $this->updated_at ? $this->updated_at->toIso8601String() : '',
            'values'                    => [static::$wrap => VariantValue::collection($this->values)]
        ];
    }
}
