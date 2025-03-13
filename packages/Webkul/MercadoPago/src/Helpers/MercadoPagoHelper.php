<?php

namespace Webkul\MercadoPago\Helpers;

use Webkul\MercadoPago\Payment\MercadoPago;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Illuminate\Support\Facades\Http;

class MercadoPagoHelper
{
    /**
     * Create a new helper instance.
     */
    public function __construct(
        protected MercadoPago $mercadoPago,
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository
    ) {}

    /**
     * Procesa las notificaciones Webhook de Mercado Pago.
     *
     * @param  array  $data
     * @return void
     */
    public function processWebhook(array $data)
    {
        if (!isset($data['id'], $data['type']) || $data['type'] !== 'payment') {
            return;
        }

        $paymentId = $data['id'];

        $paymentData = $this->getPaymentData($paymentId);

        if (!$paymentData || !isset($paymentData['status'], $paymentData['external_reference'])) {
            return;
        }

        $order = $this->orderRepository->findOneByField(['id' => $paymentData['external_reference']]);

        if (!$order) {
            return;
        }

        $this->processOrder($order, $paymentData);
    }

    /**
     * Obtiene los datos del pago desde Mercado Pago.
     *
     * @param  int  $paymentId
     * @return array|null
     */
    protected function getPaymentData(int $paymentId)
    {
        $accessToken = config('payment_methods.mercadopago.client_secret');

        $response = Http::withToken($accessToken)
            ->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

        return $response->successful() ? $response->json() : null;
    }

    /**
     * Procesa la orden en función del estado del pago.
     *
     * @param  object  $order
     * @param  array  $paymentData
     * @return void
     */
    protected function processOrder($order, array $paymentData)
    {
        if ($paymentData['status'] === 'approved') {
            $this->orderRepository->update(['status' => 'processing'], $order->id);

            if ($order->canInvoice()) {
                $invoice = $this->invoiceRepository->create([
                    'order_id' => $order->id,
                    'invoice' => [
                        'items' => $this->prepareInvoiceData($order)
                    ]
                ]);
            }
        }
    }

    /**
     * Prepara los datos de la factura.
     *
     * @param  object  $order
     * @return array
     */
    protected function prepareInvoiceData($order)
    {
        $invoiceData = [];

        foreach ($order->items as $item) {
            $invoiceData[$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }
}
