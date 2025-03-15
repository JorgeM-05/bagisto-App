<?php

namespace Webkul\MercadoPago\Providers;

use Illuminate\Support\ServiceProvider;


class MercadoPagoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot()
    {
        include __DIR__ . '/../Http/routes.php';
        $this->loadConfig();

        // $this->app->register(EventServiceProvider::class);
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'mercadopago');

        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'mercadopago');

        // $this->app->register(EventServiceProvider::class);

        // $this->app->booted(function () {
        //     $this->extendPaymentMethods();
        // });
    }
    protected function loadConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/paymentmethods.php',
            'paymentmethods'
        );

        $config = require __DIR__ . '/../Config/system.php';

        if (is_array($config)) {
            $this->mergeConfigFrom(__DIR__ . '/../Config/system.php', 'core');
        } else {
            throw new \Exception("Config file system.php must return an array.");
        }
    }
    /**
     * Register services.
     */
    public function register()
    {
        $this->registerConfig();
    }

    /**
     * Register package config.
     */
    protected function registerConfig()
    {
        // $this->mergeConfigFrom(
        //     dirname(__DIR__).'/Config/paymentmethods.php', 'payment_methods'
        // );
        // $this->mergeConfigFrom(
        //     dirname(__DIR__) . '/Config/paymentmethods.php',
        //     'sales.paymentmethods.mercadopago'
        // );

        // // Carga el Access Token desde .env para evitar error de core() en la fase de registro
        // $this->app->singleton('Webkul\MercadoPago\Payment\MercadoPago', function ($app) {
        //     $accessToken = env('MERCADOPAGO_ACCESS_TOKEN', ''); // Usamos el .env como fallback
        //     return new \Webkul\MercadoPago\Payment\MercadoPagoButton($accessToken);
        // });

        // $this->mergeConfigFrom(
        //     dirname(__DIR__) . '/Config/paymentmethods.php',
        //     'payment_methods'
        // );

        // $this->mergeConfigFrom(
        //     dirname(__DIR__) . '/Config/system.php',
        //     'core'
        // );
    }

    /**
     * Extender métodos de pago cuando Bagisto ya está listo.
     */
    protected function extendPaymentMethods()
    {
        $accessToken = core()->getConfigData('sales.payment_methods.mercadopago.client_secret');

        $this->app->extend('Webkul\MercadoPago\Payment\MercadoPago', function ($service, $app) use ($accessToken) {
            return new \Webkul\MercadoPago\Payment\MercadoPagoButton($accessToken);
        });
    }
}
