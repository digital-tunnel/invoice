<?php

return [
    'default_seller' => [
        'name' => null,
        'address' => [
            'street' => null,
            'city' => null,
            'postal_code' => null,
            'state' => null,
            'country' => null,
        ],
        'email' => null,
        'phone_number' => null,
        'tax_number' => null,
        'company_number' => null,
    ],

    'default_logo' => null,

    'default_template' => 'default',

    /**
     * ISO 4217 currency code
     */
    'default_currency' => 'SAR',

    /**
     * Default PDF options
     */
    'pdf_options' => [],
    'paper_options' => [
        'paper' => 'a4',
        'orientation' => 'portrait',
    ],
];
