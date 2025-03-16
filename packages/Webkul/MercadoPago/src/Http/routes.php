<?php
namespace Webkul\MercadoPago\Http;

use Illuminate\Support\Facades\Route;
use Webkul\MercadoPago\Http\Controllers\SmartButtonController;
use Webkul\MercadoPago\Http\Controllers\StandardController;


// Rutas para MercadoPago Standard
Route::group(['middleware' => ['web', 'theme', 'locale', 'currency'], 'prefix' => 'checkout/mercadopago/standard'], function () {
    Route::controller(StandardController::class)->group(function () {
        Route::get('redirect', 'redirect')->name('mercadopago.standard.redirect');
        Route::post('response', 'processPayment')->name('mercadopago.standard.response');
        Route::post('cancel', 'cancelPayment')->name('mercadopago.standard.cancel');
    });
});

// Rutas para MercadoPago Smart Button
Route::group(['middleware' => ['web', 'theme', 'locale', 'currency'], 'prefix' => 'checkout/mercadopago/smart-button'], function () {
    Route::controller(SmartButtonController::class)->group(function () {
        Route::get('redirect', 'redirect')->name('mercadopago.smart-button.redirect');
        Route::post('response', 'processPayment')->name('mercadopago.smart-button.response');
        Route::post('cancel', 'cancelPayment')->name('mercadopago.smart-button.cancel');
        Route::post('create-order', 'createOrder')->name('mercadopago.smart-button.create-order');
        Route::post('capture-order', 'captureOrder')->name('mercadopago.smart-button.capture-order'); // ✅ Agregar esta ruta

    });
});