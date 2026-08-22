<?php

return [
    'title' => 'Мессенджеры',
    'admin_accent_color' => 'Акцентный цвет административной панели',
    'admin_accent_hint' => 'Используется только для кнопок, ссылок, календарей и активных элементов в административной панели. Публичный сайт этот цвет не наследует.',
    'future_title' => 'Подготовка будущих каналов уведомлений',
    'future_hint' => 'Здесь безопасно сохраняются параметры подключения. Отправка сообщений пока не активирована: она будет подключена отдельным модулем вместе с шаблонами, согласиями клиентов, очередями и журналом доставки.',
    'configured' => 'Параметры заполнены',
    'incomplete' => 'Нужны параметры',
    'enabled' => 'Включено в настройках',
    'disabled' => 'Выключено',
    'enable_provider' => 'Разрешить использовать этот канал после подключения модуля отправки',
    'enable_hint' => 'Сам переключатель пока не отправляет сообщения. Будущий отправщик потребует одновременно включённый канал и полный набор ключей.',
    'secret_saved' => 'Секрет сохранён — оставьте пустым, чтобы не менять',
    'secret_missing' => 'Введите секрет',
    'secret_hint' => 'Значение хранится зашифрованным и после сохранения больше не показывается.',
    'clear_credentials' => 'Удалить все сохранённые секреты этого канала',
    'save_provider' => 'Сохранить подключение',
    'saved' => 'Настройки :provider сохранены.',
    'providers' => [
        'whatsapp' => [
            'name' => 'WhatsApp Business Cloud API',
            'description' => 'Официальный канал Meta для шаблонных и сервисных сообщений клиентам.',
            'prerequisites' => 'Нужны Meta Business Portfolio, WhatsApp Business Account и зарегистрированный бизнес-номер. Для production используйте системный access token с правами whatsapp_business_management и whatsapp_business_messaging.',
            'fields' => [
                'business_portfolio_id' => ['label'=>'Meta Business Portfolio ID', 'hint'=>'Идентификатор бизнес-портфеля Meta; нужен для управления и поиска связанных WABA.'],
                'business_account_id' => ['label'=>'WhatsApp Business Account ID (WABA ID)', 'hint'=>'Идентификатор аккаунта WhatsApp Business, не путать с Phone Number ID.'],
                'phone_number_id' => ['label'=>'Phone Number ID', 'hint'=>'Идентификатор номера-отправителя, используемый в URL запросов отправки сообщений.'],
                'app_id' => ['label'=>'Meta App ID', 'hint'=>'Идентификатор приложения Meta, необходимый для настройки webhook и управления приложением.'],
                'access_token' => ['label'=>'System User Access Token', 'hint'=>'Долгосрочный системный токен для Authorization: Bearer. Не используйте временный тестовый токен.'],
                'app_secret' => ['label'=>'Meta App Secret', 'hint'=>'Секрет приложения для проверки подписи входящих webhook-запросов.'],
                'webhook_verify_token' => ['label'=>'Webhook Verify Token', 'hint'=>'Придуманный вами секрет для первоначальной проверки webhook со стороны Meta.'],
            ],
        ],
        'telegram' => [
            'name' => 'Telegram Bot',
            'description' => 'Telegram Bot API для уведомлений клиентам, которые сами запустили бота.',
            'prerequisites' => 'Создайте бота через @BotFather. Клиент должен сначала открыть бота и дать возможность связать его Telegram chat_id со своим аккаунтом; одного номера телефона недостаточно.',
            'fields' => [
                'bot_username' => ['label'=>'Username бота', 'hint'=>'Публичное имя вида @company_booking_bot, по которому клиент откроет бота.'],
                'bot_token' => ['label'=>'Bot API Token', 'hint'=>'Токен, выданный @BotFather для HTTPS-запросов Telegram Bot API.'],
                'webhook_secret' => ['label'=>'Webhook Secret Token', 'hint'=>'Секрет из букв, цифр, _ и -; Telegram будет передавать его в заголовке X-Telegram-Bot-Api-Secret-Token.'],
            ],
        ],
        'viber' => [
            'name' => 'Viber Bot',
            'description' => 'Viber REST Bot API для подписанных на бизнес-бота клиентов.',
            'prerequisites' => 'Нужен активный Viber bot/public account на коммерческих условиях и HTTPS webhook с доверенным SSL-сертификатом. Клиент должен быть подписан на бота.',
            'fields' => [
                'bot_name' => ['label'=>'Имя бота', 'hint'=>'Отображаемое имя отправителя в сообщениях Viber.'],
                'bot_uri' => ['label'=>'Bot URI', 'hint'=>'URI публичного аккаунта, используемый в ссылке viber://pa?chatURI=...'],
                'sender_avatar_url' => ['label'=>'URL аватара отправителя', 'hint'=>'Необязательный публичный HTTPS URL изображения отправителя.'],
                'auth_token' => ['label'=>'Account Authentication Token', 'hint'=>'Секретный токен Viber Admin Panel, передаваемый в X-Viber-Auth-Token.'],
            ],
        ],
    ],
];
