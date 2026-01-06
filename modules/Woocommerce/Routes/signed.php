<?php

use Illuminate\Support\Facades\Route;

/**
 * 'signed' middleware and 'signed/woocommerce' prefix applied to all routes (including names)
 *
 * @see \App\Providers\Route::register
 */

Route::signed(
    'woocommerce',
    function () {
        Route::post('auth/callback', 'Auth@callback')->name('auth.callback');
    },
    [
        'middleware' => [
            'cookies.encrypt',
            'cookies.response',
            'session.start',
            'session.errors',
            'signature',
            'company.identify',
            'bindings',
            'header.x',
            'language',
            'firewall.all',
        ],
    ]
);
