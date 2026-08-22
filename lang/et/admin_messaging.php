<?php

return [
    'title' => 'Sõnumikanalid',
    'admin_accent_color' => 'Halduspaneeli aktsentvärv',
    'admin_accent_hint' => 'Kasutatakse ainult halduspaneeli nuppude, linkide, kalendrite ja aktiivsete elementide jaoks. Avalik veebileht seda värvi ei päri.',
    'future_title' => 'Tulevaste teavituskanalite ettevalmistamine',
    'future_hint' => 'Ühenduse parameetrid salvestatakse siin turvaliselt. Sõnumite saatmine pole veel aktiivne; see lisatakse eraldi koos mallide, kliendi nõusolekute, järjekordade ja tarneajalooga.',
    'configured' => 'Parameetrid täidetud',
    'incomplete' => 'Parameetrid puuduvad',
    'enabled' => 'Seadetes lubatud',
    'disabled' => 'Välja lülitatud',
    'enable_provider' => 'Luba kanal pärast saatmismooduli ühendamist',
    'enable_hint' => 'Lüliti ise veel sõnumeid ei saada. Tulevane saatja nõuab nii lubatud kanalit kui ka täielikke võtmeid.',
    'secret_saved' => 'Saladus on salvestatud — säilitamiseks jätke tühjaks',
    'secret_missing' => 'Sisestage saladus',
    'secret_hint' => 'Väärtus salvestatakse krüpteeritult ja pärast salvestamist seda enam ei kuvata.',
    'clear_credentials' => 'Kustuta kõik selle kanali salvestatud saladused',
    'save_provider' => 'Salvesta ühendus',
    'saved' => ':provider seaded salvestati.',
    'providers' => [
        'whatsapp' => [
            'name' => 'WhatsApp Business Cloud API',
            'description' => 'Meta ametlik kanal malli- ja teenindussõnumite saatmiseks klientidele.',
            'prerequisites' => 'Vajalikud on Meta Business Portfolio, WhatsApp Business Account ja registreeritud ärinumber. Productionis kasutage system user access token’it õigustega whatsapp_business_management ja whatsapp_business_messaging.',
            'fields' => [
                'business_portfolio_id' => ['label'=>'Meta Business Portfolio ID', 'hint'=>'Meta äriportfelli tunnus seotud WABA-de haldamiseks ja leidmiseks.'],
                'business_account_id' => ['label'=>'WhatsApp Business Account ID (WABA ID)', 'hint'=>'WhatsApp Business konto tunnus; ärge ajage seda segi Phone Number ID-ga.'],
                'phone_number_id' => ['label'=>'Phone Number ID', 'hint'=>'Saatja numbri tunnus, mida kasutatakse sõnumipäringute URL-is.'],
                'app_id' => ['label'=>'Meta App ID', 'hint'=>'Meta rakenduse tunnus webhooki ja rakenduse haldamiseks.'],
                'access_token' => ['label'=>'System User Access Token', 'hint'=>'Pikaajaline süsteemitoken Authorization: Bearer jaoks. Ärge kasutage ajutist testitokenit.'],
                'app_secret' => ['label'=>'Meta App Secret', 'hint'=>'Rakenduse saladus sissetulevate webhookide allkirja kontrollimiseks.'],
                'webhook_verify_token' => ['label'=>'Webhook Verify Token', 'hint'=>'Teie valitud saladus Meta esialgseks webhooki kontrolliks.'],
            ],
        ],
        'telegram' => [
            'name' => 'Telegram Bot',
            'description' => 'Telegram Bot API teavitused klientidele, kes on boti ise käivitanud.',
            'prerequisites' => 'Looge bot @BotFatheriga. Klient peab selle esmalt käivitama, et Telegram chat_id saaks tema kontoga siduda; telefoninumbrist üksi ei piisa.',
            'fields' => [
                'bot_username' => ['label'=>'Boti kasutajanimi', 'hint'=>'Avalik nimi, näiteks @company_booking_bot, mille kaudu klient boti avab.'],
                'bot_token' => ['label'=>'Bot API Token', 'hint'=>'@BotFatheri väljastatud token Telegram Bot API HTTPS-päringuteks.'],
                'webhook_secret' => ['label'=>'Webhook Secret Token', 'hint'=>'Ainult tähed, numbrid, _ ja -; Telegram saadab selle päises X-Telegram-Bot-Api-Secret-Token.'],
            ],
        ],
        'viber' => [
            'name' => 'Viber Bot',
            'description' => 'Viber REST Bot API äriboti tellinud klientidele.',
            'prerequisites' => 'Vajalik on aktiivne kommertstingimustel Viber bot/public account ja usaldatud SSL-sertifikaadiga HTTPS webhook. Klient peab boti tellima.',
            'fields' => [
                'bot_name' => ['label'=>'Boti nimi', 'hint'=>'Viberi sõnumites kuvatav saatja nimi.'],
                'bot_uri' => ['label'=>'Bot URI', 'hint'=>'Avaliku konto URI lingis viber://pa?chatURI=...'],
                'sender_avatar_url' => ['label'=>'Saatja avatari URL', 'hint'=>'Valikuline avalik HTTPS URL saatja pildile.'],
                'auth_token' => ['label'=>'Account Authentication Token', 'hint'=>'Viber Admin Paneli saladus, mis saadetakse päises X-Viber-Auth-Token.'],
            ],
        ],
    ],
];
