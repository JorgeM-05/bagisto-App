<?php

namespace Webkul\MercadoPago\Payment;

use MercadoPago\MercadoPagoConfig;
use Webkul\Payment\Payment\Payment;

abstract class MercadoPago extends Payment
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code = 'mercadopago_smart_button';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Initialize MercadoPago SDK with credentials.
     *
     * @return void
     */
    protected function initialize()
    {
        MercadoPagoConfig::setAccessToken('TEST-ACCESS-TOKEN'); // 🔹 Reemplaza con un token de prueba

        // $accessToken = $this->getConfigData('access_token');

        // if (empty($accessToken)) {
        //     throw new \Exception("MercadoPago: Access token is missing in configuration.");
        // }

        // MercadoPagoConfig::setAccessToken($accessToken);
    }
}
