<?php

namespace Webkul\MercadoPago\Providers;

use Illuminate\Support\ServiceProvider;

class MercadoPagoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        include __DIR__.'/../Http/routes.php';

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'mercadopago');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'mercadopago');

        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
    }

    /**
     * Register package config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/paymentmethods.php', 'payment_methods'
        );
        $this->app->bind('Webkul\MercadoPago\Payment\MercadoPago', function () {
            return new Webkul\MercadoPago\Payment\MercadoPagoButton(); // O cualquier clase concreta
        });
        
    }
}
