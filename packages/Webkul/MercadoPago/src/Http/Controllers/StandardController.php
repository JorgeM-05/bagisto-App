<?php

namespace Webkul\MercadoPago\Http\Controllers;

use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;
use Illuminate\Support\Facades\Log;

class StandardController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct(protected OrderRepository $orderRepository) {}

    /**
     * Redirige a la página de MercadoPago.
     */
    public function redirect()
    {
        return view('mercadopago::checkout.onepage.mercadopago-standard-redirect');
    }

    /**
     * Maneja la cancelación del pago.
     */
    public function cancel()
    {
        session()->flash('error', trans('shop::app.checkout.cart.payment-cancelled'));

        return redirect()->route('shop.checkout.cart.index');
    }

    /**
     * Maneja el éxito del pago y guarda la orden.
     */
    public function success()
    {
        try {
            $cart = Cart::getCart();
            $data = (new OrderResource($cart))->jsonSerialize();
            $order = $this->orderRepository->create($data);

            Cart::deActivateCart();
            session()->flash('order_id', $order->id);

            return redirect()->route('shop.checkout.onepage.success');
        } catch (\Exception $e) {
            Log::error("Error al procesar la orden en MercadoPago: " . $e->getMessage());
            session()->flash('error', trans('shop::app.common.error'));
            return redirect()->route('shop.checkout.cart.index');
        }
    }
}
