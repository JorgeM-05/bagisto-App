<?php

use Illuminate\Support\Facades\Route;
use Webkul\MercadoPago\Http\Controllers\SmartButtonController;
use Webkul\MercadoPago\Http\Controllers\StandardController;

Route::group(['middleware' => ['web']], function () {
    Route::prefix('mercadopago/standard')->group(function () {
        Route::get('/redirect', [StandardController::class, 'redirect'])->name('mercadopago.standard.redirect');
        Route::get('/success', [StandardController::class, 'success'])->name('mercadopago.standard.success');
        Route::get('/cancel', [StandardController::class, 'cancel'])->name('mercadopago.standard.cancel');
    });

    Route::prefix('mercadopago/smart-button')->group(function () {
        Route::get('/create-order', [SmartButtonController::class, 'createOrder'])->name('mercadopago.smart-button.create-order');
        Route::post('/capture-order', [SmartButtonController::class, 'captureOrder'])->name('mercadopago.smart-button.capture-order');
    });

    Route::post('mercadopago/standard/ipn', [StandardController::class, 'ipn'])
    ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
    ->name('mercadopago.standard.ipn');
});


