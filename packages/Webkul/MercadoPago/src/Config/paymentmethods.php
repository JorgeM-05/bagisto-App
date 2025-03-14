<?php

return [
    'mercadopago_smart_button' => [
        'code'        => 'mercadopago_smart_button',
        'title'       => 'MercadoPago Smart Button',
        'description' => 'Paga con MercadoPago usando Smart Button',
        'class'       => 'Webkul\MercadoPago\Payment\MercadoPagoButton',
        'sandbox'          => true,
        'active'           => true,
        'sort'        => 5,
    ],

    'mercadopago_standard' => [
        'code'         => 'mercadopago_standard',
        'title'        => 'MercadoPago Standard',
        'description'  => 'Paga con MercadoPago Standard',
        'class'        => 'Webkul\MercadoPago\Payment\MercadoPagoStandard',
        'sandbox'          => true,
        'active'           => true,
        'business_account' => 'test@webkul.com',
        'sort'         => 6,
    ],
];
