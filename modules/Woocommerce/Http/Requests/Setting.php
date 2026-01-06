<?php

namespace Modules\Woocommerce\Http\Requests;

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
            'url' => 'required|string',
            'consumer_key' => 'required|string',
            'consumer_secret' => 'required|string',
            'invoice_category_id' => 'required|string'
        ];
    }
}
