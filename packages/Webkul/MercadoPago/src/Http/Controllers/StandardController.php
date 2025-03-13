<?php

namespace Webkul\MercadoPago\Http\Controllers;

use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;

class StandardController extends Controller
{
    /**
     * Constructor.
     *
     * @param OrderRepository $orderRepository Repositorio de órdenes.
     */
    public function __construct(protected OrderRepository $orderRepository) {}

    /**
     * Redirige a la página de MercadoPago.
     *
     * @return \Illuminate\View\View
     */
    public function redirect()
    {
        return view('mercadopago::checkout.onepage.mercadopago-standard-redirect');
    }

    /**
     * Maneja la cancelación del pago.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel()
    {
        session()->flash('error', trans('shop::app.checkout.cart.payment-cancelled'));

        return redirect()->route('shop.checkout.cart.index');
    }

    /**
     * Maneja el éxito del pago y guarda la orden.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function success()
    {
        $cart = Cart::getCart();
        $data = (new OrderResource($cart))->jsonSerialize();
        $order = $this->orderRepository->create($data);

        Cart::deActivateCart();
        session()->flash('order_id', $order->id);

        return redirect()->route('shop.checkout.onepage.success');
    }
}
