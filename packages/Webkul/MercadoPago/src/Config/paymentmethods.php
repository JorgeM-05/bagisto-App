<?php

return [
    'mercadopago_smart_button' => [
        'code'             => 'mercadopago_smart_button',
        'title'            => 'mercadopago Smart Button',
        'description'      => 'Paga con Mercado Pago de forma segura.',
        'client_id'        => 'sb',
        'class'            => 'Webkul\MercadoPago\Payment\MercadoPagoButton',
        'sandbox'          => false,
        'active'           => true,
        'sort'             => 2,
    ],
    'mercadopago_standard' => [
        'code'        => 'mercadopago',
        'title'       => 'Mercado Pago',
        'description' => 'Paga con Mercado Pago de forma segura.',
        'class'       => 'Webkul\MercadoPago\Payment\MercadoPagoStandard',
        'active'      => true,
        'sort'        => 1,
    ],
];
