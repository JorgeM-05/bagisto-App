<?php

namespace Webkul\MercadoPago\Providers;

use Illuminate\Support\ServiceProvider;

class MercadoPagoServiceProvider extends ServiceProvider
{
    public function boot()
    {
        include __DIR__ . '/../Http/routes.php';

        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'mercadopago');
        // $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'mercadopago');
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
     * Merge the mercadopagoSource connect's configuration with the admin panel
     */
    public function registerConfig()
    {
        // $this->mergeConfigFrom(__DIR__ . '/../Config/system.php', 'core');
        $this->mergeConfigFrom(__DIR__ . '/../Config/paymentmethods.php', 'payment_methods');
    }
}
