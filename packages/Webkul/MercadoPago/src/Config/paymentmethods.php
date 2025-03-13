<?php 
return [
    'mercadopago_smart_button' => [
        'code'        => 'mercadopago_smart_button',
        'title'       => 'MercadoPago Smart Button',
        'description' => 'Paga con MercadoPago usando Smart Button',
        'class'       => Webkul\MercadoPago\Payment\MercadoPagoButton::class,
        'active'      => true,
        'sort'        => 3,
    ],
    'mercadopago' => [
        'code'         => 'mercadopago',
        'title'        => 'MercadoPago',
        'description'  => 'Paga con MercadoPago',
        'class'        => Webkul\MercadoPago\Payment\MercadoPagoButton::class,
        'sandbox'      => env('MERCADOPAGO_SANDBOX', true),
        'access_token' => env('MERCADOPAGO_ACCESS_TOKEN', 'TU_ACCESS_TOKEN'),
        'active'       => true,
        'sort'         => 4,
    ],
];
