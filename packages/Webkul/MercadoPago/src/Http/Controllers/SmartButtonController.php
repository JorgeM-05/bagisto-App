<?php

namespace Webkul\MercadoPago\Http\Controllers;

use Illuminate\Http\Request;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\InvoiceRepository;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;

class SmartButtonController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository
    ) {}

    /**
     * Crea una orden de Mercado Pago.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrder()
    {
        try {
            // Configurar Mercado Pago con la clave de acceso
            MercadoPagoConfig::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));

            // Obtener el carrito actual
            $cart = Cart::getCart();

            // Instancia de preferencias de pago
            $preferenceClient = new PreferenceClient();

            // Crear los productos para la orden
            $items = [];
            foreach ($cart->items as $item) {
                $items[] = [
                    "title" => $item->name,
                    "quantity" => $item->quantity,
                    "unit_price" => (float) $item->price,
                    "currency_id" => $cart->cart_currency_code
                ];
            }

            // Crear la orden en Mercado Pago
            $preference = $preferenceClient->create([
                "items" => $items,
                "external_reference" => uniqid(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Orden creada con éxito',
                'order_id' => $preference->id,
                'init_point' => $preference->init_point, // URL para el pago
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la orden',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Redirige al usuario a la página de pago de Mercado Pago.
     *
     * @return \Illuminate\View\View
     */
    public function redirect()
    {
        $cart = Cart::getCart();
        $amount = $cart->sub_total + $cart->tax_total + ($cart->selected_shipping_rate->price ?? 0) - $cart->discount_amount;

        return view('mercadopago::mercadopago-source-redirect', [
            'amount' => $amount,
            'currency' => $cart->cart_currency_code,
        ]);
    }

    /**
     * Procesa la respuesta de Mercado Pago después del pago.
     *
     * @return \Illuminate\Http\Response
     */
    public function processPayment()
    {
        try {
            if (request()->get('status') === 'approved') {
                $order = $this->orderRepository->create(Cart::prepareDataForOrder());
                $this->orderRepository->update(['status' => 'processing'], $order->id);

                if ($order->canInvoice()) {
                    $this->invoiceRepository->create($this->prepareInvoiceData($order));
                }

                Cart::deActivateCart();
                session()->flash('order', $order);

                return redirect()->route('shop.checkout.onepage.success');
            }
        } catch (\Exception $e) {
            session()->flash('error', trans('mercadopago::app.admin.transaction.error'));
        }

        return redirect()->route('shop.checkout.onepage.index');
    }

    /**
     * Prepara los datos de la factura.
     *
     * @param  \Webkul\Sales\Models\Order  $order
     * @return array
     */
    protected function prepareInvoiceData($order)
    {
        $invoiceData = ["order_id" => $order->id];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }

    /**
     * Cancela el pago.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancelPayment()
    {
        session()->flash('success', trans('mercadopago::app.admin.transaction.cancelled'));

        return redirect()->route('shop.checkout.onepage.index');
    }

    /**
     * Captura el pago de Mercado Pago.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function captureOrder(Request $request)
    {
        try {
            // Configurar Mercado Pago
            MercadoPagoConfig::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));

            // Instancia del cliente de pagos
            $paymentClient = new PaymentClient();

            // Capturar el pago
            $payment = $paymentClient->create([
                "transaction_amount" => (float) $request->input('amount'),
                "token" => $request->input('token'),
                "description" => "Orden de compra #" . $request->input('order_id'),
                "payment_method_id" => $request->input('payment_method_id'),
                "payer" => [
                    "email" => $request->input('payer_email'),
                ],
            ]);

            if ($payment['status'] == "approved") {
                return response()->json([
                    'success' => true,
                    'message' => 'Pago aprobado con éxito',
                    'payment_id' => $payment['id'],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'El pago no fue aprobado',
                'status' => $payment['status'],
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al capturar el pago',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
