<?php

namespace Webkul\MercadoPago\Http\Controllers;

use Webkul\Checkout\Facades\Cart;
use Webkul\MercadoPago\Payment\MercadoPagoButton;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;

class SmartButtonController extends Controller
{
    /**
     * Constructor.
     * 
     * @param MercadoPagoButton $smartButton Instancia de la clase de pago de MercadoPago.
     * @param OrderRepository $orderRepository Repositorio de órdenes.
     * @param InvoiceRepository $invoiceRepository Repositorio de facturas.
     */
    public function __construct(
        protected MercadoPagoButton $smartButton,
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository
    ) {}

    /**
     * Crea un pedido en MercadoPago para su aprobación.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con los detalles del pedido.
     */
    public function createOrder()
    {
        try {
            $body = request()->all(); // Asegura que se pase un array con datos
            return response()->json($this->smartButton->createOrder($body));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear el pedido'], 400);
        }
    }

    /**
     * Captura el pago de una orden aprobada en MercadoPago.
     *
     * @return \Illuminate\Http\JsonResponse Redirige al usuario según el estado del pedido.
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
            return response()->json(['error' => 'Error al capturar el pago'], 400);
        }
    }

    /**
     * Guarda el pedido en la base de datos después de que el pago es exitoso.
     *
     * @return \Illuminate\Http\JsonResponse Respuesta con éxito o redirección en caso de error.
     */
    protected function saveOrder()
    {
        if (Cart::hasError()) {
            return response()->json(['redirect_url' => route('shop.checkout.cart.index')], 403);
        }

        try {
            // Calcula los totales antes de procesar el pedido
            Cart::collectTotals();

            // Obtiene los datos del carrito
            $cart = Cart::getCart();

            // Convierte el carrito en datos de pedido
            $data = (new OrderResource($cart))->jsonSerialize();

            // Crea la orden en la base de datos
            $order = $this->orderRepository->create($data);

            // Actualiza el estado del pedido a "processing"
            $this->orderRepository->update(['status' => 'processing'], $order->id);

            // Verifica si el método canInvoice() existe antes de llamarlo
            if (method_exists($order, 'canInvoice') && $order->canInvoice()) {
                $this->invoiceRepository->create([
                    'order_id' => $order->id,
                    'invoice'  => ['items' => $order->items->pluck('qty_to_invoice', 'id')->toArray()]
                ]);
            }

            // Desactiva el carrito después de completar la compra
            Cart::deActivateCart();
            session()->flash('order_id', $order->id);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            session()->flash('error', trans('shop::app.common.error'));
            return response()->json(['error' => 'Error al guardar la orden'], 500);
        }
    }
}
