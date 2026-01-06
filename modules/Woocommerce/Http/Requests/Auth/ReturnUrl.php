<?php

namespace Modules\Woocommerce\Http\Requests\Auth;

use App\Abstracts\Http\FormRequest as Request;

class ReturnUrl extends Request
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
            'success' => 'required|boolean',
            'user_id' => 'required|integer',
        ];
    }
}
