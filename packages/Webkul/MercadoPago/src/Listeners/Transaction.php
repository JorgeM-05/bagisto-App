<?php

namespace Webkul\MercadoPago\Listeners;

use Webkul\MercadoPago\Payment\MercadoPago;
use Webkul\Sales\Repositories\OrderTransactionRepository;
use Illuminate\Support\Facades\Log;

class Transaction
{
    /**
     * Constructor.
     *
     * @param  MercadoPago  $mercadoPago
     * @param  OrderTransactionRepository  $orderTransactionRepository
     */
    public function __construct(
        protected MercadoPago $mercadoPago,
        protected OrderTransactionRepository $orderTransactionRepository
    ) {}

    /**
     * Guarda los datos de la transacción de Mercado Pago.
     *
     * @param  \Webkul\Sales\Models\Invoice  $invoice
     * @return void
     */
    public function saveTransaction($invoice)
    {
        $data = request()->all();

        // Verifica que la orden haya sido pagada con Mercado Pago Standard
        if ($invoice->order->payment->method == 'mercadopago_standard') {
            if (isset($data['payment_id'])) {
                // Obtener los detalles de la transacción desde Mercado Pago
                $transactionDetails = $this->mercadoPago->getOrder($data['payment_id']);

                // Asegurar que los datos sean un array antes de acceder a ellos
                if (is_array($transactionDetails) && isset($transactionDetails['status'])) {
                    try {
                        // Guardar los detalles de la transacción en la base de datos
                        $this->orderTransactionRepository->create([
                            'transaction_id' => $transactionDetails['id'],
                            'status'         => $transactionDetails['status'],
                            'type'           => $transactionDetails['payment_type_id'] ?? 'unknown',
                            'amount'         => $transactionDetails['transaction_amount'] ?? 0,
                            'payment_method' => $invoice->order->payment->method,
                            'order_id'       => $invoice->order->id,
                            'invoice_id'     => $invoice->id,
                            'data'           => json_encode($transactionDetails),
                        ]);

                        Log::info("MercadoPago: Transacción guardada correctamente para la orden ID {$invoice->order->id}.");
                    } catch (\Exception $e) {
                        Log::error("MercadoPago: Error al guardar la transacción - " . $e->getMessage());
                    }
                } else {
                    Log::error("MercadoPago: No se pudo obtener información válida para la transacción con ID {$data['payment_id']}.");
                }
            } else {
                Log::error("MercadoPago: No se recibió un payment_id en la solicitud.");
            }
        }
    }
}
