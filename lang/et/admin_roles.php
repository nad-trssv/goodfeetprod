<?php

return [
    'title' => 'Rollid ja õigused', 'subtitle' => 'Määrake, milliseid osi töötajad näevad ja mida nad saavad teha.',
    'new_role' => 'Uus roll', 'role_name' => 'Rolli nimi', 'role_example' => 'Näiteks: filiaali juht',
    'data_scope' => 'Broneeringute ja klientide ulatus', 'scope_own' => 'Ainult enda broneeringud ja kliendid',
    'scope_all' => 'Kõik spetsialistid, broneeringud ja kliendid', 'create' => 'Lisa roll',
    'service_provider' => 'Selle rolliga töötajad osutavad teenuseid ja kuvatakse spetsialistidena',
    'save' => 'Salvesta õigused', 'delete' => 'Kustuta roll', 'system' => 'Süsteemne roll',
    'employees' => ':count töötajat', 'customer_locked' => 'Kliendiroll kasutab eraldi autentimist ja seda ei saa siin muuta.',
    'cannot_remove_own_access' => 'Enda rollilt ei saa rollide haldamise õigust eemaldada.',
    'cannot_delete' => 'Süsteemset, kasutusel olevat või enda rolli ei saa kustutada.',
    'created' => 'Roll on loodud. Nüüd määrake õigused.', 'updated' => 'Rolli õigused on salvestatud.',
    'deleted' => 'Roll on kustutatud.', 'access_denied' => 'Teil pole selle toimingu tegemiseks õigust.',
    'permission_hint' => 'Server kontrollib neid õigusi iga päringu puhul. Nuppude peitmine ei asenda turvakontrolli.',
    'groups' => [
        'dashboard'=>'Töölaud','appointments'=>'Broneeringud','customers'=>'Kliendid','services'=>'Teenused','staff'=>'Töötajad',
        'schedules'=>'Graafikud ja erandid','master_services'=>'Töötajate teenused','rooms'=>'Kabinetid','settings'=>'Saidi seaded',
        'promo_codes'=>'Sooduskoodid','notifications'=>'Teavitused','reschedule_requests'=>'Aja muutmise taotlused',
        'activity_logs'=>'Tegevuslogi','roles'=>'Rollid ja õigused','profile'=>'Oma profiil','work_time'=>'Tööaja arvestus',
    ],
    'actions' => ['view'=>'Vaatamine','create'=>'Loomine','update'=>'Muutmine','delete'=>'Kustutamine / tühistamine','status'=>'Staatuse muutmine','message'=>'Sõnum kliendile','manage'=>'Täielik haldus','full'=>'Täielik analüütika'],
    'system_roles' => ['super-admin'=>'Administraator','master'=>'Spetsialist','customer'=>'Klient','receptionist'=>'Administraator-reseptsionist'],
];
