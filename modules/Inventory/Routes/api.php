<?php

use Illuminate\Support\Facades\Route;

/**
 * 'api' middleware and 'api/inventory' prefix applied to all routes (including names)
 *
 * @see \App\Providers\Route::register
 */

Route::group([
    'middleware' => 'api',
    'prefix' => 'api',
    'namespace' => 'Modules\Inventory\Http\Controllers\Api'
], function () {
    // Override default route
    Route::group(['as' => 'api.'], function () {
        // items
        Route::get('inventory-items/{item}/enable', 'Items@enable')->name('items.enable');
        Route::get('inventory-items/{item}/disable', 'Items@disable')->name('items.disable');
        Route::patch('inventory-items/{item}', 'Items@update')->name('items.update');
        Route::delete('inventory-items/{item}', 'Items@destroy')->name('items.destroy');
        Route::apiResource('inventory-items', 'Items');

        // item-groups
        Route::get('item-groups/{item_group}/enable', 'ItemGroups@enable')->name('item-groups.enable');
        Route::get('item-groups/{item_group}/disable', 'ItemGroups@disable')->name('item-groups.disable');
        Route::apiResource('item-groups', 'ItemGroups');

        // Variants
        Route::get('variants/{variant}/enable', 'Variants@enable')->name('variants.enable');
        Route::get('/{variant}/disable', 'Variants@disable')->name('variants.disable');
        Route::apiResource('variants', 'Variants');

        // warehouses
        Route::get('warehouses/{warehouse}/enable', 'Warehouses@enable')->name('warehouses.enable');
        Route::get('warehouses/{warehouse}/disable', 'Warehouses@disable')->name('warehouses.disable');
        Route::apiResource('warehouses', 'Warehouses');

        // Transfer Orders
        Route::apiResource('transfer-orders', 'TransferOrders');

        // Adjustments
        Route::apiResource('adjustments', 'Adjustments');
        
        //Histories
        Route::apiResource('histories', 'Histories');
    });
});