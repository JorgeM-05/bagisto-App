<?php

namespace Webkul\MercadoPago\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Webkul\Theme\ViewRenderEventManager;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot()
    {
        Event::listen('bagisto.shop.layout.body.after', static function (ViewRenderEventManager $viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('mercadopago::checkout.onepage.mercadopago-smart-button');
        });

        // Guardar transacción después de la generación de la factura
        // Event::listen('sales.invoice.save.after', 'Webkul\MercadoPago\Listeners\Transaction@saveTransaction');
        Event::listen('sales.invoice.save.after', [\Webkul\MercadoPago\Listeners\Transaction::class, 'saveTransaction']);

    }
}
