<?php

namespace Webkul\MercadoPago\Payment;

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference;
use MercadoPago\Client\Payment;
use MercadoPago\MercadoPagoClient;

class MercadoPagoButton extends MercadoPago
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code = 'mercadopago_smart_button';

    /**
     * MercadoPago Partner Attribution ID.
     *
     * @var string
     */
    protected $mercadoPagoPartnerAttributionId = 'Bagisto_Cart';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Create order for approval of client.
     *
     * @param  array  $body
     * @return object
     */
    public function createOrder($body)
    {
        $preferenceClient = new Preference();
        $preference = $preferenceClient->create([
            'items' => $body['items'],
            'payer' => $body['payer'],
            'back_urls' => [
                "success" => route('mercadopago.smart-button.success'),
                "failure" => route('mercadopago.smart-button.failure'),
                "pending" => route('mercadopago.smart-button.pending')
            ],
            'auto_return' => "approved"
        ]);

        return $preference;
    }

    /**
     * Capture payment after approval.
     *
     * @param  string  $paymentId
     * @return object
     */
    public function captureOrder($paymentId)
    {
        $paymentClient = new Payment();
        $payment = $paymentClient->get($paymentId);

        if ($payment) {
            $paymentClient->update($paymentId, ['capture' => true]);
        }

        return $payment;
    }

    /**
     * Get order details.
     *
     * @param  string  $paymentId
     * @return object
     */
    public function getOrder($paymentId)
    {
        $paymentClient = new Payment();
        return $paymentClient->get($paymentId);
    }

    /**
     * Refund order.
     *
     * @param  string  $paymentId
     * @return object
     */
    public function refundOrder($paymentId)
    {
        $paymentClient = new Payment();
        return $paymentClient->refund($paymentId);
    }

    /**
     * Return MercadoPago redirect URL.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return route('mercadopago.smart-button.redirect');
    }

    /**
     * Initialize MercadoPago SDK with credentials.
     *
     * @return void
     */
    protected function initialize()
    {
        MercadoPagoConfig::setAccessToken('TEST-ACCESS-TOKEN'); // 🔹 Reemplaza con un token de prueba

        // MercadoPagoConfig::setAccessToken($this->getConfigData('access_token'));
    }
}
