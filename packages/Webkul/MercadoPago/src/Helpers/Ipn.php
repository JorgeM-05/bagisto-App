<?php

namespace Webkul\MercadoPago\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\InvoiceRepository;

class Ipn
{
    /**
     * Datos recibidos desde Mercado Pago.
     *
     * @var array
     */
    protected $data;

    /**
     * Orden de Bagisto
     *
     * @var \Webkul\Sales\Contracts\Order
     */
    protected $order;

    /**
     * Constructor de la clase.
     */
    public function __construct(
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository
    ) {}

    /**
     * Procesar la notificación de Mercado Pago.
     *
     * @param  array  $data
     * @return void
     */
    public function processIpn($data)
    {
        $this->data = $data;

        Log::info('Webhook recibido de Mercado Pago:', $this->data);

        if (! isset($this->data['data']['id'])) {
            Log::error('Webhook inválido: No se encontró el ID de pago.');
            return;
        }

        $paymentId = $this->data['data']['id'];
        $paymentData = $this->getPaymentDetails($paymentId);

        if (! $paymentData) {
            Log::error("No se pudo obtener información del pago: ID {$paymentId}");
            return;
        }

        $this->getOrder($paymentData['external_reference']);
        $this->processOrder($paymentData);
    }

    /**
     * Obtener detalles del pago desde la API de Mercado Pago.
     *
     * @param  string  $paymentId
     * @return array|null
     */
    protected function getPaymentDetails($paymentId)
    {
        $accessToken = core()->getConfigData('sales.payment_methods.mercadopago_standard.access_token');

        if (!$accessToken) {
            Log::error("Access Token de Mercado Pago no configurado.");
            return null;
        }

        $response = Http::withToken($accessToken)->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

        if ($response->failed()) {
            Log::error("Error en la consulta de pago: " . $response->body());
            return null;
        }

        return $response->json();
    }

    /**
     * Buscar la orden en Bagisto con el `external_reference`.
     *
     * @param  string  $externalReference
     * @return void
     */
    protected function getOrder($externalReference)
    {
        if (empty($this->order)) {
            $this->order = $this->orderRepository->findWhere(['increment_id' => $externalReference])->first();
        }
    }

    /**
     * Procesar el pedido en función del estado del pago.
     *
     * @param  array  $paymentData
     * @return void
     */
    protected function processOrder($paymentData)
    {
        if (!$this->order) {
            Log::error("Orden no encontrada para el pago: " . $paymentData['external_reference']);
            return;
        }

        $status = $paymentData['status']; // `approved`, `pending`, `rejected`

        switch ($status) {
            case 'approved':
                $this->orderRepository->update(['status' => 'processing'], $this->order->id);

                if ($this->order->canInvoice()) {
                    $invoice = $this->invoiceRepository->create($this->prepareInvoiceData());
                    Log::info("Factura generada para la orden: " . $this->order->id);
                }
                break;

            case 'pending':
                $this->orderRepository->update(['status' => 'pending_payment'], $this->order->id);
                break;

            case 'rejected':
                $this->orderRepository->update(['status' => 'canceled'], $this->order->id);
                break;
        }

        Log::info("Estado de la orden actualizado: " . $this->order->id . " - Estado: " . $status);
    }

    /**
     * Preparar los datos de la factura.
     *
     * @return array
     */
    protected function prepareInvoiceData()
    {
        $invoiceData = ['order_id' => $this->order->id];

        foreach ($this->order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }
}
