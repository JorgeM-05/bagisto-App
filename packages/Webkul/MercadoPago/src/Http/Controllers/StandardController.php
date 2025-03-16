<?php

namespace Webkul\MercadoPago\Http\Controllers;

use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\InvoiceRepository;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Resources\Preference;
use MercadoPago\Resources\Preference\Item;
use MercadoPago\Exceptions\MPApiException;


class StandardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository
    ) {}

    /**
     * Redirige al usuario a la página de pago de Mercado Pago.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        // Obtener credenciales desde el panel de admin
        $accessToken = core()->getConfigData('sales.payment_methods.mercadopago_standard.access_token');

        if (!$accessToken) {
            return redirect()->route('shop.checkout.onepage.index')->with('error', 'Access Token no configurado.');
        }

        // Inicializar SDK de Mercado Pago
        MercadoPagoConfig::setAccessToken($accessToken);
        $preferenceClient = new PreferenceClient();

        // Obtener carrito
        $cart = Cart::getCart();

        if (!$cart || !$cart->items->count()) {
            return redirect()->route('shop.checkout.onepage.index')->with('error', 'El carrito está vacío.');
        }

        // Construir array de la preferencia
        $items = [];

        foreach ($cart->items as $cartItem) {
            $items[] = [
                'title'       => $cartItem->name,
                'quantity'    => $cartItem->quantity,
                'unit_price'  => (float) $cartItem->price,
                'currency_id' => $cart->cart_currency_code,
            ];
        }

        $preferenceData = [
            'items'               => $items,
            'external_reference'  => uniqid(),
            'back_urls'           => [
                "success" => route('mercadopago.standard.success'),
                "failure" => route('mercadopago.standard.failure'),
                "pending" => route('mercadopago.standard.pending'),
            ],
            'auto_return'         => "approved",
            'notification_url'    => route('mercadopago.standard.ipn'),
        ];

        try {
            $createdPreference = $preferenceClient->create($preferenceData);

            // Depuración: Verificar respuesta de Mercado Pago
            dd($createdPreference);

            return redirect()->away($createdPreference['init_point']);
        } catch (\Exception $e) {
            return redirect()->route('shop.checkout.onepage.index')->with('error', 'Error en MercadoPago: ' . $e->getMessage());
        }
    }
}
