<?php

namespace Modules\Woocommerce\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\URL;
use Modules\Woocommerce\Adapters\WooCommerceAdapter;
use Modules\Woocommerce\Http\Requests\Auth\Callback;
use Modules\Woocommerce\Http\Requests\Auth\Redirect;
use Modules\Woocommerce\Http\Requests\Auth\ReturnUrl;

class Auth extends Controller
{
    public function show()
    {
        return view('woocommerce::auth');
    }

    public function redirect(Redirect $request)
    {
        setting()->set('woocommerce.url', $request->get('url'));
        setting()->save();

        $endpoint = env('WOOCOMMERCE_AUTH_ENDPOINT', WooCommerceAdapter::AUTH_ENDPOINT);

        $params = [
            'app_name'     => 'Akaunting Woocommerce App',
            'scope'        => 'read_write',
            'user_id'      => company_id(),
            'return_url'   => route('woocommerce.auth.return'),
            'callback_url' => URL::temporarySignedRoute(
                'signed.woocommerce.auth.callback',
                now()->addMinutes(5),
                ['company_id' => company_id()]
            ),
        ];

        return redirect('https://' . $request->get('url') . $endpoint . '?' . http_build_query($params));
    }

    public function returnUrl(ReturnUrl $request)
    {
        if (false === $request->get('success')) {
            setting()->set('woocommerce.url', null);
            setting()->save();

            flash(trans('woocommerce::general.error.api_connection_error'))->error()->important();

            return view('woocommerce::auth');
        }

        flash(trans('woocommerce::general.form.auth_success'))->success();

        return redirect()->route('woocommerce.edit');
    }

    public function callback(Callback $request)
    {
        company($request->get('user_id'))->makeCurrent();

        setting()->set('woocommerce.consumer_key', $request->get('consumer_key'));
        setting()->set('woocommerce.consumer_secret', $request->get('consumer_secret'));
        setting()->save();

        return response()->json('ok');
    }
}
