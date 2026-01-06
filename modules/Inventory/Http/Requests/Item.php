<?php

namespace Modules\Inventory\Http\Requests;

use App\Abstracts\Http\FormRequest as Request;

class Item extends Request
{
    public function rules()
    {
        // Simplificamos al máximo para diagnosticar
        return [
            'name' => 'required|string',
            'sku' => 'nullable',
            'sale_price' => 'nullable',
            'purchase_price' => 'nullable',
            'sunat_unit_code' => 'nullable',
            'sunat_tax_type' => 'nullable',
            'tax_ids' => 'nullable',
            'category_id' => 'nullable',
            'enabled' => 'nullable',
            // Desactivamos validaciones complejas de items de inventario por un momento
            'items' => 'nullable|array',
            'warehouse_id' => 'nullable',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // Obligamos a escribir en el log de emergencia
        file_put_contents(storage_path('logs/emergency_validation.log'), json_encode($validator->errors()->toArray()) . PHP_EOL, FILE_APPEND);
        parent::failedValidation($validator);
    }

    public function messages()
    {
        return [];
    }
}
