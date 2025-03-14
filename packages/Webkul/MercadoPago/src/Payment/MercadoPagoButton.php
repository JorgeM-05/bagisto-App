<?php

namespace Webkul\MercadoPago\Payment;

use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;

class MercadoPagoButton extends MercadoPago
{
    /**
     * Código del método de pago.
     *
     * @var string
     */
    protected $code = 'mercadopago_smart_button';

    /**
     * ID de atribución de MercadoPago para Bagisto.
     *
     * @var string
     */
    protected $mercadoPagoPartnerAttributionId = 'Bagisto_Cart';

    /**
     * Crea una orden de pago en MercadoPago.
     *
     * @param  array  $body
     * @return object
     * @throws \Exception
     */
    public function createOrder($body)
    {
        try {
            $preferenceClient = new PreferenceClient();
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
        } catch (\Exception $e) {
            throw new \Exception("Error al crear la orden en MercadoPago: " . $e->getMessage());
        }
    }

    /**
     * Captura el pago después de la aprobación.
     *
     * @param  string  $paymentId
     * @return object
     * @throws \Exception
     */
    public function captureOrder($paymentId)
    {
        try {
            $paymentClient = new PaymentClient();
            $payment = $paymentClient->get($paymentId);

            if ($payment && $payment->status === 'approved') {
                $paymentClient->update($paymentId, ['capture' => true]);
            }

            return $payment;
        } catch (\Exception $e) {
            throw new \Exception("Error al capturar la orden: " . $e->getMessage());
        }
    }

    /**
     * Obtiene los detalles de una orden.
     *
     * @param  string  $paymentId
     * @return object
     * @throws \Exception
     */
    public function getOrder($paymentId)
    {
        try {
            $paymentClient = new PaymentClient();
            return $paymentClient->get($paymentId);
        } catch (\Exception $e) {
            throw new \Exception("Error al obtener la orden: " . $e->getMessage());
        }
    }

    /**
     * Reembolsa una orden.
     *
     * @param  string  $paymentId
     * @return object
     * @throws \Exception
     */
    public function refundOrder($paymentId)
    {
        try {
            $paymentClient = new PaymentClient();
            return $paymentClient->refund($paymentId);
        } catch (\Exception $e) {
            throw new \Exception("Error al reembolsar la orden: " . $e->getMessage());
        }
    }

    /**
     * Retorna la URL de redirección de MercadoPago.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return route('mercadopago.smart-button.redirect');
    }
}
