<?php

namespace Webkul\MercadoPago\Http\Controllers;

use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\InvoiceRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Webkul\MercadoPago\Helpers\Ipn;

class StandardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository,
        protected Ipn $ipnHelper
        
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
            Log::error("Mercado Pago: Access Token no configurado.");
            return redirect()->route('shop.checkout.onepage.index')->with('error', 'Access Token no configurado.');
        }
        Log::error("Mercado Pago: Access Token configurado. " . $accessToken);

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

        // Crear la preferencia de pago en Mercado Pago
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
            'notification_url'   => secure_url('mercadopago/standard/ipn'),

        ];

        try {
            // Llamada a la API de Mercado Pago para crear la preferencia
            $response = Http::withToken($accessToken)->post('https://api.mercadopago.com/checkout/preferences', $preferenceData);
            $responseData = $response->json();

            if ($response->failed()) {
                Log::error('Mercado Pago Error: ' . $response->body());
                return redirect()->route('shop.checkout.onepage.index')->with('error', 'Error al procesar el pago con MercadoPago.');
            }

            // Redirigir al usuario a la URL de pago de Mercado Pago
            return redirect()->away($responseData['init_point']);
        } catch (\Exception $e) {
            Log::error('Mercado Pago Exception: ' . $e->getMessage());
            return redirect()->route('shop.checkout.onepage.index')->with('error', 'Error en MercadoPago: ' . $e->getMessage());
        }
    }


    /**
     * Escucha los Webhooks de Mercado Pago.
     *
     * @return \Illuminate\Http\Response
     */
    public function ipn(Request $request)
    {
        $this->ipnHelper->processIpn($request->all());

        return response()->json(['message' => 'Notificación recibida'], 200);
    }
}
