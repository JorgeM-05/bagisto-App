<?php

namespace Webkul\MercadoPago\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Webkul\Theme\ViewRenderEventManager;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen('bagisto.shop.layout.body.after', static function (ViewRenderEventManager $viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('mercadopago::checkout.onepage.mercadopago-smart-button');
        });

        Event::listen('sales.invoice.save.after', 'Webkul\MercadoPago\Listeners\Transaction@saveTransaction');
    }
}
