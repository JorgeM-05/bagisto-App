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

         // ✅ NUEVAS RUTAS PARA EL RETORNO DE MERCADO PAGO
         Route::get('success', 'success')->name('mercadopago.standard.success');
         Route::get('failure', 'failure')->name('mercadopago.standard.failure');
         Route::get('pending', 'pending')->name('mercadopago.standard.pending');
         Route::post('checkout/mercadopago/standard/ipn', [StandardController::class, 'ipn'])
         ->name('mercadopago.standard.ipn');
     
    });
});

// Rutas para MercadoPago Smart Button
Route::group(['middleware' => ['web', 'theme', 'locale', 'currency'], 'prefix' => 'checkout/mercadopago/smart-button'], function () {
    Route::controller(SmartButtonController::class)->group(function () {
        Route::get('redirect', 'redirect')->name('mercadopago.smart-button.redirect');
        Route::post('response', 'processPayment')->name('mercadopago.smart-button.response');
        Route::post('cancel', 'cancelPayment')->name('mercadopago.smart-button.cancel');
        Route::post('create-order', 'createOrder')->name('mercadopago.smart-button.create-order');
        Route::post('capture-order', 'captureOrder')->name('mercadopago.smart-button.capture-order');

    });
});

// Route::post('mercadopago/standard/ipn', [StandardController::class, 'ipn'])
//     // ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
//     ->name('mercadopago.standard.ipn');