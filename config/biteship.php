<?php

return [
    'api_key' => env('BITESHIP_API_KEY'),
    'base_url' => env('BITESHIP_BASE_URL', 'https://api.biteship.com/v1'),
    'default_couriers' => array_filter(explode(',', env('BITESHIP_COURIERS', 'jne,jnt,anteraja,sicepat,tiki,pos'))),
    'verify_ssl' => (bool) env('BITESHIP_VERIFY_SSL', true),
    'mock_rates_enabled' => (bool) env('BITESHIP_MOCK_RATES', false),
    'mock_rates' => [
        [
            'courier_code' => 'jne',
            'courier_company' => 'JNE',
            'services' => [
                [
                    'service_code' => 'REG',
                    'service_name' => 'Regular',
                    'base_price' => 20000,
                    'per_kg' => 4000,
                    'estimation' => '2-4 hari',
                ],
                [
                    'service_code' => 'YES',
                    'service_name' => 'Yakin Esok Sampai',
                    'base_price' => 35000,
                    'per_kg' => 6000,
                    'estimation' => '1-2 hari',
                ],
            ],
        ],
        [
            'courier_code' => 'anteraja',
            'courier_company' => 'AnterAja',
            'services' => [
                [
                    'service_code' => 'REG',
                    'service_name' => 'Regular',
                    'base_price' => 18000,
                    'per_kg' => 3500,
                    'estimation' => '2-5 hari',
                ],
                [
                    'service_code' => 'NEXT',
                    'service_name' => 'Next Day',
                    'base_price' => 32000,
                    'per_kg' => 5500,
                    'estimation' => '1-2 hari',
                ],
            ],
        ],
    ],
    'webhook_secret' => env('BITESHIP_WEBHOOK_SECRET'),
    'origin' => [
        'contact_name' => env('BITESHIP_ORIGIN_CONTACT_NAME', env('APP_NAME', 'GTech Store')),
        'contact_phone' => env('BITESHIP_ORIGIN_CONTACT_PHONE'),
        'contact_email' => env('BITESHIP_ORIGIN_CONTACT_EMAIL'),
        'address' => env('BITESHIP_ORIGIN_ADDRESS'),
        'postal_code' => env('BITESHIP_ORIGIN_POSTAL_CODE'),
        'area_id' => env('BITESHIP_ORIGIN_AREA_ID'),
        'latitude' => env('BITESHIP_ORIGIN_LATITUDE'),
        'longitude' => env('BITESHIP_ORIGIN_LONGITUDE'),
        'note' => env('BITESHIP_ORIGIN_NOTE'),
    ],
];
