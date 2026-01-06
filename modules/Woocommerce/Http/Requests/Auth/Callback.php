<?php

namespace Modules\Woocommerce\Http\Requests\Auth;

use App\Abstracts\Http\FormRequest as Request;

class Callback extends Request
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
            'key_id'          => 'required|integer',
            'user_id'         => 'required|integer',
            'consumer_key'    => 'required|string',
            'consumer_secret' => 'required|string',
            'key_permissions' => 'required|string',
        ];
    }
}
