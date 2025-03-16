<?php
namespace Webkul\MercadoPago\Http;

use Illuminate\Support\Facades\Route;
use Webkul\MercadoPago\Http\Controllers\SmartButtonController;
use Webkul\MercadoPago\Http\Controllers\StandardController;


Route::group(['middleware' => ['web', 'theme', 'locale', 'currency'], 'prefix' => 'checkout/mercadopago-source'], function () {
    Route::controller(SmartButtonController::class)->group(function () {
        Route::get('redirect', 'redirect')->name('mercadopago_source.redirect');

        Route::post('response', 'processPayment');

        Route::post('cancel', 'cancelPayment');
    });
});

