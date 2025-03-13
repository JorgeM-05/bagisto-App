<?php

namespace Webkul\MercadoPago\Payment;

class MercadoPagoStandard extends MercadoPago
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code = 'mercadopago_standard';

    /**
     * Return MercadoPago redirect URL.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return route('mercadopago.standard.redirect');
    }

    /**
     * Return MercadoPago IPN URL.
     *
     * @return string
     */
    public function getIPNUrl()
    {
        return route('mercadopago.standard.ipn');
    }

    /**
     * Return form field array for MercadoPago checkout.
     *
     * @return array
     */
    public function getFormFields()
    {
        $cart = $this->getCart();

        $fields = [
            'transaction_amount' => $cart->sub_total + $cart->tax_total + ($cart->selected_shipping_rate ? $cart->selected_shipping_rate->price : 0) - $cart->discount_amount,
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

        return $fields;
    }
}
