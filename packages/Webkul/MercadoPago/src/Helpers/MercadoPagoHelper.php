<?php

namespace Webkul\MercadoPago\Helpers;

use Webkul\MercadoPago\Payment\MercadoPago;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use MercadoPago\Client\Payment\PaymentClient;
use Illuminate\Support\Facades\Log;

class MercadoPagoHelper
{
    /**
     * Constructor.
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
            Log::error("MercadoPago: No se pudo obtener información del pago con ID {$paymentId}");
            return;
        }

        $order = $this->orderRepository->findOneByField(['id' => $paymentData['external_reference']]);

        if (!$order) {
            Log::error("MercadoPago: No se encontró la orden con ID {$paymentData['external_reference']}");
            return;
        }

        $this->processOrder($order, $paymentData);
    }

    /**
     * Obtiene los datos del pago desde Mercado Pago utilizando el SDK.
     *
     * @param  int  $paymentId
     * @return array|null
     */
    protected function getPaymentData(int $paymentId)
    {
        try {
            $accessToken = core()->getConfigData('sales.payment_methods.mercadopago_standard.access_token');

            if (empty($accessToken)) {
                throw new \Exception("El Access Token de MercadoPago no está configurado.");
            }

            $paymentClient = new PaymentClient();
            $payment = $paymentClient->get($paymentId);

            return isset($payment->id) ? (array) $payment : null;
        } catch (\Exception $e) {
            Log::error("MercadoPago: Error al obtener los datos del pago - " . $e->getMessage());
            return null;
        }
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
        try {
            if ($paymentData['status'] === 'approved') {
                $this->orderRepository->update(['status' => 'processing'], $order->id);

                if ($order->canInvoice()) {
                    $this->invoiceRepository->create([
                        'order_id' => $order->id,
                        'invoice' => [
                            'items' => $this->prepareInvoiceData($order)
                        ]
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("MercadoPago: Error al procesar la orden {$order->id} - " . $e->getMessage());
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
