<?php

namespace Webkul\MercadoPago\Listeners;

use Webkul\MercadoPago\Payment\MercadoPago;
use Webkul\Sales\Repositories\OrderTransactionRepository;

class Transaction
{
    /**
     * Create a new listener instance.
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

        if ($invoice->order->payment->method == 'mercadopago') {
            if (isset($data['payment_id'])) {
                $transactionDetails = $this->mercadoPago->getPaymentDetails($data['payment_id']);

                if ($transactionDetails && isset($transactionDetails['status'])) {
                    $this->orderTransactionRepository->create([
                        'transaction_id' => $transactionDetails['id'],
                        'status'         => $transactionDetails['status'],
                        'type'           => $transactionDetails['payment_type_id'],
                        'amount'         => $transactionDetails['transaction_amount'],
                        'payment_method' => $invoice->order->payment->method,
                        'order_id'       => $invoice->order->id,
                        'invoice_id'     => $invoice->id,
                        'data'           => json_encode($transactionDetails),
                    ]);
                }
            }
        }
    }
}
