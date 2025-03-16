<?php

namespace Webkul\MercadoPago\payment;

use Illuminate\Support\Facades\Storage;
use Webkul\Payment\Payment\Payment;

class MercadoPago extends Payment
{




    /**
     * Payment method code
     *
     * @var string
     */
    protected $code  = 'mercadopago_source';

    /**
     * Return paypal redirect url.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return route('mercadopago_source.redirect');
    }

    /**
     * Returns payment method image
     *
     * @return array
     */
    public function getImage()
    {
        $url = $this->getConfigData('image');

        return $url ? Storage::url($url) : bagisto_asset('images/mercadopago-source.png', 'mercadopago_source');
    }
}