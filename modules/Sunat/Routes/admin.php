<?php

use Illuminate\Support\Facades\Route;

Route::admin('sunat', function () {
    Route::group(['prefix' => 'configuration', 'as' => 'configuration.'], function () {
        Route::get('/', 'Settings@index')->name('index');
        Route::patch('/', 'Settings@update')->name('update');
        Route::get('certificate', 'Settings@certificate')->name('certificate');
        Route::post('certificate', 'Settings@uploadCertificate')->name('certificate.upload');
        Route::delete('certificate/{certificate}', 'Settings@deleteCertificate')->name('certificate.delete');
    });

    Route::group(['prefix' => 'emissions', 'as' => 'emissions.'], function () {
        Route::get('/', 'EmissionController@index')->name('index');
        Route::get('{emission}', 'EmissionController@show')->name('show');
        Route::post('{emission}/retry', 'EmissionController@retry')->name('retry');
        Route::get('{emission}/xml', 'EmissionController@downloadXml')->name('xml');
        Route::get('{emission}/cdr', 'EmissionController@downloadCdr')->name('cdr');
    });

    Route::group(['prefix' => 'emit', 'as' => 'emit.'], function () {
        Route::post('invoice/{document}', 'EmitController@invoice')->name('invoice');
        Route::post('credit-note/{document}', 'EmitController@creditNote')->name('credit-note');
        Route::post('debit-note/{document}', 'EmitController@debitNote')->name('debit-note');
    });
});
