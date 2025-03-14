<?php

namespace Webkul\MercadoPago\Payment;

use MercadoPago\MercadoPagoConfig;
use Webkul\Payment\Payment\Payment;

abstract class MercadoPago extends Payment
{
    /**
     * Código del método de pago.
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
     * Inicializa el SDK de MercadoPago con credenciales.
     *
     * @return void
     * @throws \Exception
     */
    protected function initialize()
    {
        $accessToken = core()->getConfigData('sales.paymentmethods.mercadopago.client_secret');

        if (empty($accessToken)) {
            throw new \Exception("MercadoPago: El token de acceso no está configurado en la administración de Bagisto.");
        }

        MercadoPagoConfig::setAccessToken($accessToken);
    }
}
