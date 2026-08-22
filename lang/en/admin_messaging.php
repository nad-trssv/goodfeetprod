<?php

return [
    'title' => 'Messengers',
    'admin_accent_color' => 'Administration accent color',
    'admin_accent_hint' => 'Used only for buttons, links, calendars and active controls in the administration panel. The public website does not inherit this color.',
    'future_title' => 'Preparing future notification channels',
    'future_hint' => 'Connection parameters are stored securely here. Sending is not active yet; it will be implemented separately with templates, customer consent, queues and delivery logs.',
    'configured' => 'Parameters complete',
    'incomplete' => 'Parameters required',
    'enabled' => 'Enabled in settings',
    'disabled' => 'Disabled',
    'enable_provider' => 'Allow this channel after the sending module is connected',
    'enable_hint' => 'This switch does not send messages yet. The future sender will require both an enabled channel and complete credentials.',
    'secret_saved' => 'Secret saved — leave blank to keep it',
    'secret_missing' => 'Enter secret',
    'secret_hint' => 'The value is encrypted at rest and is never displayed again after saving.',
    'clear_credentials' => 'Remove all saved secrets for this channel',
    'save_provider' => 'Save connection',
    'saved' => ':provider settings saved.',
    'providers' => [
        'whatsapp' => [
            'name' => 'WhatsApp Business Cloud API',
            'description' => 'Meta’s official channel for template and service messages to customers.',
            'prerequisites' => 'Requires a Meta Business Portfolio, WhatsApp Business Account and registered business phone. For production use a system-user access token with whatsapp_business_management and whatsapp_business_messaging permissions.',
            'fields' => [
                'business_portfolio_id' => ['label'=>'Meta Business Portfolio ID', 'hint'=>'The Meta business portfolio identifier used to manage and discover linked WABAs.'],
                'business_account_id' => ['label'=>'WhatsApp Business Account ID (WABA ID)', 'hint'=>'The WhatsApp Business account identifier; do not confuse it with Phone Number ID.'],
                'phone_number_id' => ['label'=>'Phone Number ID', 'hint'=>'The sender number identifier used in message API request URLs.'],
                'app_id' => ['label'=>'Meta App ID', 'hint'=>'The Meta application identifier used for webhook setup and app management.'],
                'access_token' => ['label'=>'System User Access Token', 'hint'=>'Long-lived system token used as Authorization: Bearer. Do not use a temporary test token.'],
                'app_secret' => ['label'=>'Meta App Secret', 'hint'=>'Application secret used to verify signatures on incoming webhooks.'],
                'webhook_verify_token' => ['label'=>'Webhook Verify Token', 'hint'=>'A secret chosen by you for Meta’s initial webhook verification.'],
            ],
        ],
        'telegram' => [
            'name' => 'Telegram Bot',
            'description' => 'Telegram Bot API notifications for customers who have started the bot.',
            'prerequisites' => 'Create a bot using @BotFather. A customer must start it before their Telegram chat_id can be linked to their account; a phone number alone is not enough.',
            'fields' => [
                'bot_username' => ['label'=>'Bot username', 'hint'=>'Public name such as @company_booking_bot that customers use to open the bot.'],
                'bot_token' => ['label'=>'Bot API Token', 'hint'=>'Token issued by @BotFather for Telegram Bot API HTTPS requests.'],
                'webhook_secret' => ['label'=>'Webhook Secret Token', 'hint'=>'Letters, numbers, _ and - only; Telegram sends it in X-Telegram-Bot-Api-Secret-Token.'],
            ],
        ],
        'viber' => [
            'name' => 'Viber Bot',
            'description' => 'Viber REST Bot API for customers subscribed to the business bot.',
            'prerequisites' => 'Requires an active commercial Viber bot/public account and an HTTPS webhook with a trusted certificate. The customer must subscribe to the bot.',
            'fields' => [
                'bot_name' => ['label'=>'Bot name', 'hint'=>'Sender name displayed in Viber messages.'],
                'bot_uri' => ['label'=>'Bot URI', 'hint'=>'Public account URI used in links such as viber://pa?chatURI=...'],
                'sender_avatar_url' => ['label'=>'Sender avatar URL', 'hint'=>'Optional public HTTPS URL for the sender image.'],
                'auth_token' => ['label'=>'Account Authentication Token', 'hint'=>'Secret from Viber Admin Panel sent in X-Viber-Auth-Token.'],
            ],
        ],
    ],
];
