<?php

namespace Webkul\MercadoPago\Payment;

use Illuminate\Support\Facades\Storage;
use Webkul\Payment\Payment\Payment;

class MercadoPago extends Payment
{
    /**
     * Código del método de pago.
     *
     * @var string
     */
    protected $code = 'mercadopago';

    /**
     * Obtiene el Access Token desde el panel de administración.
     *
     * @return string|null
     */
    public function getAccessToken()
    {
        return core()->getConfigData('sales.payment_methods.mercadopago_standard.access_token') ?: null;
    }

    /**
     * Obtiene la Public Key desde el panel de administración.
     *
     * @return string|null
     */
    public function getPublicKey()
    {
        return core()->getConfigData('sales.payment_methods.mercadopago_standard.public_key') ?: null;
    }

    /**
     * Verifica si está en modo sandbox o producción.
     *
     * @return bool
     */
    public function getSandboxMode()
    {
        return (bool) core()->getConfigData('sales.payment_methods.mercadopago_standard.sandbox');
    }

    /**
     * Obtiene la URL de pago de Mercado Pago.
     *
     * @return string
     */
    public function getMercadopagoUrl()
    {
        return $this->getSandboxMode()
            ? 'https://api.mercadopago.com/sandbox/checkout/preferences'
            : 'https://api.mercadopago.com/checkout/preferences';
    }
    

    /**
     * Retorna la URL de redirección de MercadoPago.
     *
     * @return string|null
     */
    public function getRedirectUrl()
    {
        return $this->getMethodStatus() 
            ? route('mercadopago.standard.redirect') 
            : null;
    }
    

    /**
     * Verifica si el método de pago está activo en el panel de administración.
     *
     * @return bool
     */
    public function getMethodStatus()
    {
        return (bool) core()->getConfigData('sales.payment_methods.mercadopago_standard.active');
    }

    /**
     * Obtiene la imagen del método de pago.
     *
     * @return string
     */
    public function getImage()
    {
        $url = $this->getConfigData('image');

        return $url ? Storage::url($url) : bagisto_asset('images/mercadopago.png', 'shop');
    }

    /**
     * Formatea un valor monetario según las restricciones de Mercado Pago.
     *
     * @param  float|int  $number
     * @return float
     */
    public function formatCurrencyValue($number): float
    {
        return round((float) $number, 2);
    }

    /**
     * Formatea un número de teléfono según las restricciones de Mercado Pago.
     *
     * @param  mixed  $phone
     * @return string
     */
    public function formatPhone($phone): string
    {
        return preg_replace('/[^0-9]/', '', (string) $phone);
    }
}
