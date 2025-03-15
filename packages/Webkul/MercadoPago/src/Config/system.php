<?php

return [
    [
        'key'    => 'sales.payment_methods.mercadopago_smart_button',
        'name'   => 'admin::app.configuration.index.sales.payment-methods.mercadopago-smart-button',
        'info'   => 'admin::app.configuration.index.sales.payment-methods.mercadopago-smart-button-info',
        'sort'   => 3,
        'fields' => [
            [
                'name'          => 'title',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.title',
                'type'          => 'text',
                'depends'       => 'active:1',
                'validation'    => 'required_if:active,1',
                'channel_based' => true,
                'locale_based'  => true,
            ],
            [
                'name'          => 'client_id',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.client-id',
                'type'          => 'text',
                'depends'       => 'active:1',
                'validation'    => 'required_if:active,1',
                'channel_based' => true,
                'locale_based'  => false,
            ],
            [
                'name'          => 'client_secret', 
                'title'         => 'admin::app.configuration.index.sales.payment-methods.client-secret',
                'type'          => 'text',
                'depends'       => 'active:1',
                'validation'    => 'required_if:active,1',
                'channel_based' => true,
                'locale_based'  => false,
            ],
            [
                'name'          => 'accepted_currencies',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.accepted-currencies',
                'type'          => 'select',
                'default_value' => 'USD',
                'channel_based' => true,
                'locale_based'  => false,
                'options'       => [
                    [
                        'title' => 'USD - Dólar Estadounidense',
                        'value' => 'USD',
                    ],
                    [
                        'title' => 'COP - Peso Colombiano',
                        'value' => 'COP',
                    ],
                    [
                        'title' => 'ARS - Peso Argentino',
                        'value' => 'ARS',
                    ],
                    [
                        'title' => 'MXN - Peso Mexicano',
                        'value' => 'MXN',
                    ],
                ],
            ],
            [
                'name'          => 'sandbox',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.sandbox',
                'type'          => 'boolean',
                'channel_based' => true,
                'locale_based'  => false,
            ],
            [
                'name'          => 'active',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.status',
                'type'          => 'boolean',
                'channel_based' => true,
                'locale_based'  => false,
            ],
        ],
    ]
];
