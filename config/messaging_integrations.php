<?php

return [
    'whatsapp_graph_version' => env('WHATSAPP_GRAPH_VERSION', 'v23.0'),
    'provider_order' => ['whatsapp', 'viber', 'telegram'],
    'providers' => [
        'whatsapp' => [
            'settings' => [
                'business_portfolio_id' => 'numeric_id',
                'business_account_id' => 'numeric_id',
                'phone_number_id' => 'numeric_id',
                'app_id' => 'numeric_id',
                'booking_created_template' => 'text',
                'appointment_reminder_template' => 'text',
                'review_request_template' => 'text',
                'template_language_codes' => 'json',
            ],
            'credentials' => ['access_token', 'app_secret', 'webhook_verify_token'],
            'required_settings' => ['business_account_id', 'phone_number_id'],
            'required_credentials' => ['access_token'],
        ],
        'telegram' => [
            'settings' => ['bot_username' => 'bot_username'],
            'credentials' => ['bot_token', 'webhook_secret'],
            'required_settings' => ['bot_username'],
            'required_credentials' => ['bot_token', 'webhook_secret'],
        ],
        'viber' => [
            'settings' => [
                'bot_name' => 'text',
                'bot_uri' => 'text',
                'sender_avatar_url' => 'url',
            ],
            'credentials' => ['auth_token'],
            'required_settings' => ['bot_name', 'bot_uri'],
            'required_credentials' => ['auth_token'],
        ],
    ],
];
