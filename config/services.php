<?php

return [
    'speso_sms' => [
        'api_key'   => env('SPESO_API_KEY'),
        'sender_id' => env('SPESO_SMS_SENDER_ID', 'Drezwel'),
        'base_url'  => env('SPESO_BASE_URL', 'https://business.speso.co/api/v1'),
    ],

    // Speso Collections (MoMo prompt) used at checkout. SPESO_API_KEY is shared with SMS.
    'speso' => [
        'base_url'       => env('SPESO_BASE_URL', 'https://business.speso.co/api/v1'),
        'api_key'        => env('SPESO_API_KEY'),
        'webhook_secret' => env('SPESO_WEBHOOK_SECRET'),
        'callback_url'   => env('SPESO_CALLBACK_URL'),
        // Minutes an unpaid online order keeps its stock before it is cancelled.
        'abandon_after'  => (int) env('SPESO_ABANDON_AFTER', 60),
    ],
];
