<?php

namespace Modules\Inventory\Http\Requests;

use App\Abstracts\Http\FormRequest as Request;

class Setting extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'default_warehouse' => 'required|integer',
            'barcode_type' => 'required|string',
            'track_inventory' => 'integer|boolean',
            'negative_stock' => 'integer|boolean',
            'reorder_level_notification' => 'integer|boolean',
            'transfer_order_prefix' => 'required|string',
            'transfer_order_digit' => 'required|integer',
            'transfer_order_next' => 'required|integer',
            'adjustment_prefix' => 'required|string',
            'adjustment_digit' => 'required|integer',
            'adjustment_next' => 'required|integer',
        ];
    }
}
