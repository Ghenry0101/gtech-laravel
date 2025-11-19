<?php

return [
    'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
    'snap_base_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com'
        : 'https://app.sandbox.midtrans.com',
    'core_api_base_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://api.midtrans.com'
        : 'https://api.sandbox.midtrans.com',
    'verify_ssl' => (bool) env('MIDTRANS_VERIFY_SSL', true),
    'payment_methods' => [
        'bank_transfer' => [
            'label' => 'Transfer Bank (Virtual Account)',
            'description' => 'BCA, BNI, BRI, Permata',
            'enabled_payments' => ['bank_transfer'],
            'icon' => 'VA',
        ],
        'qris' => [
            'label' => 'QRIS (Semua Bank & E-Wallet)',
            'description' => 'Scan kode QR via mobile banking atau e-wallet',
            'enabled_payments' => ['qris'],
            'icon' => 'QR',
        ],
        'ewallet' => [
            'label' => 'Dompet Digital',
            'description' => 'GoPay & ShopeePay',
            'enabled_payments' => ['gopay', 'shopeepay'],
            'icon' => 'EW',
        ],
    ],
];
