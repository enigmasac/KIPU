<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Response;

/**
 * 'api' middleware and 'api/woocommerce' prefix applied to all routes (including names)
 *
 * @see \App\Providers\Route::register
 */

Route::group(['middleware' => 'api', 'prefix' => 'woocommerce', 'as' => 'woocommerce.'], function () {
    Route::get('check', function () {
        return new Response('Not Found', 404);
    });
});

// <?php

// use Dingo\Api\Http\Response;

// $api = app('Dingo\Api\Routing\Router');

// $api->version('v1', function($api) {
//     $api->group(['prefix' => 'woocommerce'], function($api) {
//         $api->get('check', function () {
//             return new Response('Not Found', 404);
//         });
//     });
// });

// $api->version('v1', ['middleware' => ['api']], function($api) {
//     $api->group(['prefix' => 'woocommerce', 'namespace' => 'Modules\WooCommerce\Http\Controllers'], function($api) {
//         // real routes
//     });
// });
