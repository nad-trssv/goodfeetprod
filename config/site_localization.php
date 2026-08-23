<?php

return [
    /*
     * Languages which can be installed from the administration panel.
     * The key is an ISO 639-1 code. Existing installations may keep using
     * additional codes already stored in the database.
     */
    'catalog' => [
        'et' => ['name' => 'Estonian', 'native_name' => 'Eesti'],
        'en' => ['name' => 'English', 'native_name' => 'English'],
        'ru' => ['name' => 'Russian', 'native_name' => 'Русский'],
        'fi' => ['name' => 'Finnish', 'native_name' => 'Suomi'],
        'lv' => ['name' => 'Latvian', 'native_name' => 'Latviešu'],
        'lt' => ['name' => 'Lithuanian', 'native_name' => 'Lietuvių'],
        'de' => ['name' => 'German', 'native_name' => 'Deutsch'],
        'fr' => ['name' => 'French', 'native_name' => 'Français'],
        'es' => ['name' => 'Spanish', 'native_name' => 'Español'],
        'it' => ['name' => 'Italian', 'native_name' => 'Italiano'],
        'pl' => ['name' => 'Polish', 'native_name' => 'Polski'],
        'sv' => ['name' => 'Swedish', 'native_name' => 'Svenska'],
        'da' => ['name' => 'Danish', 'native_name' => 'Dansk'],
        'nl' => ['name' => 'Dutch', 'native_name' => 'Nederlands'],
        'uk' => ['name' => 'Ukrainian', 'native_name' => 'Українська'],
    ],

    /* Translation groups rendered on public and customer-facing pages. */
    'frontend_groups' => [
        'msg',
        'customer',
        'auth',
        'promo',
        'pagination',
        'passwords',
        'validation',
        'appointment_statuses',
        'policy',
    ],

    /* CRM also contains back-office labels; expose only widget strings here. */
    'frontend_keys' => [
        'crm.start_chat', 'crm.online', 'crm.offline', 'crm.today_hours', 'crm.close',
        'crm.your_name', 'crm.email', 'crm.phone', 'crm.message', 'crm.begin', 'crm.send',
        'crm.rate_conversation', 'crm.rate_conversation_hint', 'crm.rating_star',
        'crm.rating_thanks', 'crm.start_new_chat', 'crm.contact_required', 'crm.send_failed',
        'crm.rating_failed', 'crm.restart_failed', 'crm.too_many_messages',
        'crm.client_staff_joined', 'crm.client_transfer_notice', 'crm.client_conversation_closed',
    ],
];
