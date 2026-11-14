<?php

return [
    'speso_sms' => [
        'api_key'   => env('SPESO_API_KEY'),
        'sender_id' => env('SPESO_SMS_SENDER_ID', 'Drezwel'),
        'base_url'  => env('SPESO_BASE_URL', 'https://business.speso.co/api/v1'),
    ],
];
