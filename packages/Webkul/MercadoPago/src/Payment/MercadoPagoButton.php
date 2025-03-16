<?php

namespace Webkul\MercadoPago\Payment;

use Webkul\MercadoPago\payment\MercadoPago;

class MercadoPagoButton extends MercadoPago
{
    /**
     * Código del método de pago.
     *
     * @var string
     */
    protected $code = 'mercadopago_smart_button';

    /**
     * Retorna la URL de redirección de MercadoPago.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return route('mercadopago.button.redirect');
    }

    /**
     * Retorna la URL de notificación IPN de MercadoPago.
     *
     * @return string
     */
    public function getIPNUrl()
    {
        return route('mercadopago.standard.ipn');
    }

    /**
     * Retorna los campos del formulario para el checkout de MercadoPago.
     *
     * @return array
     */
    public function getFormFields()
    {
        $cart = $this->getCart();

        return [
            'transaction_amount' => $cart->sub_total + $cart->tax_total + 
                                    ($cart->selected_shipping_rate ? $cart->selected_shipping_rate->price : 0) 
                                    - $cart->discount_amount,
            'token'              => '', // Se asignará en el frontend
            'description'        => core()->getCurrentChannel()->name,
            'installments'       => 1, // Se puede modificar según la configuración
            'payment_method_id'  => '', // Se asignará en el frontend
            'payer'              => [
                'email' => $cart->billing_address->email,
            ],
            'back_urls'          => [
                'success' => route('mercadopago.standard.success'),
                'failure' => route('mercadopago.standard.failure'),
                'pending' => route('mercadopago.standard.pending'),
            ],
            'auto_return'        => 'approved',
            'notification_url'   => route('mercadopago.standard.ipn'),
        ];
    }
}
