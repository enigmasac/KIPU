<?php

namespace Modules\Woocommerce\Http\Requests\Auth;

use App\Abstracts\Http\FormRequest as Request;

class Redirect extends Request
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
        ];
    }
}
