<?php

namespace Webkul\MercadoPago\Payment;

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Resources\Preference;
use MercadoPago\Resources\Preference\Item;
use Webkul\Checkout\Facades\Cart;


class MercadoPagoStandard extends MercadoPago
{
    /**
     * Código del método de pago.
     *
     * @var string
     */
    protected $code = 'mercadopago_standard';

    /**
     * Retorna la URL de redirección de MercadoPago.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return route('mercadopago.standard.redirect');
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
     * Genera una preferencia de pago en Mercado Pago.
     *
     * @return string|null
     */
    public function createPreference()
    {
        $accessToken = $this->getAccessToken();
    
        if (!$accessToken) {
            return null;
        }
    
        // Configurar Access Token en Mercado Pago
        MercadoPagoConfig::setAccessToken($accessToken);
    
        // Obtener carrito
        $cart = Cart::getCart();
    
        if (!$cart || !$cart->items->count()) {
            return null;
        }
    
        // Crear estructura de datos de la preferencia
        $preferenceData = [
            'items' => [],
            'external_reference' => uniqid(),
            'back_urls' => [
                'success' => route('mercadopago.standard.success'),
                'failure' => route('mercadopago.standard.failure'),
                'pending' => route('mercadopago.standard.pending'),
            ],
            'auto_return' => 'approved',
            'notification_url' => route('mercadopago.standard.ipn'),
        ];
    
        foreach ($cart->items as $item) {
            $preferenceData['items'][] = [
                'title' => $item->name,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->price,
                'currency_id' => $cart->cart_currency_code,
            ];
        }
    
        // Crear instancia del Cliente de Preferencias
        $preferenceClient = new PreferenceClient();
        
        // Crear la preferencia en Mercado Pago
        try {
            $createdPreference = $preferenceClient->create($preferenceData);
            return $createdPreference->init_point ?? null;
        } catch (\Exception $e) {
            \Log::error("Error al crear preferencia en Mercado Pago: " . $e->getMessage());
            return null;
        }
    }
    
}
