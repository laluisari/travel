<?php
return [
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'environment' => env('MIDTRANS_ENVIRONMENT', 'sandbox'),
    'qris_enabled' => env('MIDTRANS_QRIS_ENABLED', true),
    'qris_acquirer' => env('MIDTRANS_QRIS_ACQUIRER', 'gopay'), // gopay atau airpay_shopee
];