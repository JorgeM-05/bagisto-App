<?php

namespace Webkul\MercadoPago\Http\Controllers;

use Webkul\Checkout\Facades\Cart;
use Webkul\MercadoPago\Payment\MercadoPagoButton;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;
use Illuminate\Support\Facades\Log;

class SmartButtonController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct(
        protected MercadoPagoButton $smartButton,
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository
    ) {}

    /**
     * Crea un pedido en MercadoPago para su aprobación.
     */
    public function createOrder()
    {
        try {
            return response()->json($this->smartButton->createOrder($this->buildRequestBody()));
        } catch (\Exception $e) {
            Log::error("Error al crear la orden en MercadoPago: " . $e->getMessage());
            return response()->json(['error' => 'Error al crear la orden'], 400);
        }
    }

    /**
     * Captura el pago de una orden aprobada en MercadoPago.
     */
    public function captureOrder()
    {
        try {
            $orderId = request()->input('orderData.orderID');

            if (!$orderId) {
                return response()->json(['error' => 'Falta el ID de la orden'], 400);
            }

            $this->smartButton->captureOrder($orderId);

            return $this->saveOrder();
        } catch (\Exception $e) {
            Log::error("Error al capturar la orden en MercadoPago: " . $e->getMessage());
            return response()->json(['error' => 'Error al capturar el pago'], 400);
        }
    }

    /**
     * Guarda la orden después del pago exitoso.
     */
    protected function saveOrder()
    {
        if (Cart::hasError()) {
            return response()->json(['redirect_url' => route('shop.checkout.cart.index')], 403);
        }

        try {
            Cart::collectTotals();
            $cart = Cart::getCart();
            $data = (new OrderResource($cart))->jsonSerialize();

            $order = $this->orderRepository->create($data);
            $this->orderRepository->update(['status' => 'processing'], $order->id);

            if (method_exists($order, 'canInvoice') && $order->canInvoice()) {
                $this->invoiceRepository->create([
                    'order_id' => $order->id,
                    'invoice' => ['items' => $order->items->pluck('qty_to_invoice', 'id')->toArray()]
                ]);
            }

            Cart::deActivateCart();
            session()->flash('order_id', $order->id);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error("Error al guardar la orden en MercadoPago: " . $e->getMessage());
            return response()->json(['error' => 'Error al guardar la orden'], 500);
        }
    }
}
