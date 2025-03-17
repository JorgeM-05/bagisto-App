<?php

namespace Webkul\MercadoPago\Payment;

use Webkul\Checkout\Facades\Cart;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
    // public function createPreference()
    // {
    //     $accessToken = $this->getAccessToken();
    
    //     if (!$accessToken) {
    //         return null;
    //     }
    
    //     // Configurar Access Token en Mercado Pago
    //     MercadoPagoConfig::setAccessToken($accessToken);
    
    //     // Obtener carrito
    //     $cart = Cart::getCart();
    
    //     if (!$cart || !$cart->items->count()) {
    //         return null;
    //     }
    
    //     // Crear estructura de datos de la preferencia
    //     $preferenceData = [
    //         'items' => [],
    //         'external_reference' => uniqid(),
    //         'back_urls' => [
    //             'success' => route('mercadopago.standard.success'),
    //             'failure' => route('mercadopago.standard.failure'),
    //             'pending' => route('mercadopago.standard.pending'),
    //         ],
    //         'auto_return' => 'approved',
    //         'notification_url' => route('mercadopago.standard.ipn'),
    //     ];
    
    //     foreach ($cart->items as $item) {
    //         $preferenceData['items'][] = [
    //             'title' => $item->name,
    //             'quantity' => (int) $item->quantity,
    //             'unit_price' => (float) $item->price,
    //             'currency_id' => $cart->cart_currency_code,
    //         ];
    //     }
    
    //     // Crear instancia del Cliente de Preferencias
    //     $preferenceClient = new PreferenceClient();
        
    //     // Crear la preferencia en Mercado Pago
    //     try {
    //         $createdPreference = $preferenceClient->create($preferenceData);
    //         return $createdPreference->init_point ?? null;
    //     } catch (\Exception $e) {
    //         \Log::error("Error al crear preferencia en Mercado Pago: " . $e->getMessage());
    //         return null;
    //     }
    // }
    

    /**
     * Genera una preferencia de pago en Mercado Pago.
     *
     * @return string|null
     */
    public function createPreference()
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error("Mercado Pago: Access Token no configurado.");
            return null;
        }

        // Obtener carrito
        $cart = Cart::getCart();

        if (!$cart || !$cart->items->count()) {
            Log::error("Mercado Pago: El carrito está vacío.");
            return null;
        }

        // Construir array de la preferencia
        $items = [];

        foreach ($cart->items as $item) {
            $items[] = [
                'title'       => $item->name,
                'quantity'    => (int) $item->quantity,
                'unit_price'  => (float) $item->price,
                'currency_id' => $cart->cart_currency_code,
            ];
        }

        $preferenceData = [
            'items'              => $items,
            'external_reference' => 'ORDER_' . uniqid(),
            'back_urls'          => [
                "success" => route('mercadopago.standard.success'),
                "failure" => route('mercadopago.standard.failure'),
                "pending" => route('mercadopago.standard.pending'),
            ],
            'auto_return'        => "approved",
            'notification_url'   => route('mercadopago.standard.ipn'),
        ];

        try {
            // Llamada a Mercado Pago para crear la preferencia
            $response = Http::withToken($accessToken)
                            ->post('https://api.mercadopago.com/checkout/preferences', $preferenceData);

            $responseData = $response->json();

            if ($response->failed()) {
                Log::error("Mercado Pago Error: " . $response->body());
                return null;
            }

            return $responseData['init_point'] ?? null;

        } catch (\Exception $e) {
            Log::error("Error al crear preferencia en Mercado Pago: " . $e->getMessage());
            return null;
        }
    }
}

