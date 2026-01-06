<?php

namespace Modules\CompositeItems\Http\Requests;

use App\Abstracts\Http\FormRequest;

class CompositeItem extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'sku' => 'nullable|string',
            'sunat_unit_code' => 'nullable|string',
            'sale_price' => 'required',
            'purchase_price' => 'required',
            'category_id' => 'required|integer',
            'enabled' => 'integer|boolean',
            'items' => 'required|array',
        ];
    }
}