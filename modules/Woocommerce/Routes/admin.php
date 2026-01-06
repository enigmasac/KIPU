<?php

use Illuminate\Support\Facades\Route;

Route::admin('woocommerce', function () {
    Route::group(['prefix' => 'settings', 'middleware' => ['woocommerce-authenticate']], function () {
            Route::get('woocommerce', 'Settings@edit')->name('edit');
            Route::post('woocommerce', 'Settings@update')->name('update');
            Route::get('woocommerce/restart', 'Settings@restart')->name('restart');
        }
    );

    Route::get('auth', 'Auth@show')->name('auth.show');
    Route::post('auth', 'Auth@redirect')->name('auth.redirect');
    Route::get('auth/return', 'Auth@returnUrl')->name('auth.return');

    Route::get('sync/count', 'Sync@count')->name('sync.count');

    Route::get('sync/akaunting', 'AkauntingData@count')->name('sync.akaunting');
    Route::post('sync/akaunting/categories/{id}', 'AkauntingData@syncCategory')->name('sync.akaunting.categories');
    Route::post('sync/akaunting/products/{id}', 'AkauntingData@syncProducts')->name('sync.akaunting.products');
    Route::post('sync/akaunting/contacts/{id}', 'AkauntingData@syncContact')->name('sync.akaunting.contacts');

    Route::get('taxes/sync/{page}', 'Taxes@sync')->name('tax.sync');
    Route::post('taxes', 'Taxes@store')->name('tax.store');

    Route::get('payment-methods/sync', 'PaymentMethods@sync')->name('payment-method.sync');
    Route::post('payment-methods', 'PaymentMethods@store')->name('payment-method.store');

    Route::get('categories/sync/{page}', 'Categories@sync')->name('category.sync');
    Route::post('categories', 'Categories@store')->name('category.store');

    Route::get('customers/sync/{page}', 'Customers@sync')->name('customer.sync');
    Route::post('customers', 'Customers@store')->name('customer.store');

    Route::get('products/sync/{page}', 'Products@sync')->name('product.sync');
    Route::post('products', 'Products@store')->name('product.store');

    Route::get('attributes/sync/', 'Attributes@sync')->name('attribute.sync');
    Route::post('attributes', 'Attributes@store')->name('attribute.store');

    Route::get('orders/sync/{page}', 'Orders@sync')->name('order.sync');
    Route::post('orders', 'Orders@store')->name('order.store');
});
