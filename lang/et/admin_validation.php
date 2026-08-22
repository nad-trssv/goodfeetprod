<?php

return [
    'profile'=>[
        'name.required'=>'Nimi on kohustuslik.','name.string'=>'Nimi peab olema tekst.','name.max'=>'Nimi võib olla kuni 255 märki.',
        'phone.required'=>'Telefon on kohustuslik.','phone.string'=>'Telefon peab olema tekst.','phone.max'=>'Telefon võib olla kuni 15 märki.',
        'email.required'=>'E-post on kohustuslik.','email.email'=>'Sisestage korrektne e-posti aadress.','email.max'=>'E-post võib olla kuni 25 märki.',
        'username.required'=>'Kasutajanimi on kohustuslik.','username.string'=>'Kasutajanimi peab olema tekst.','username.min'=>'Kasutajanimi peab sisaldama vähemalt 3 märki.','username.max'=>'Kasutajanimi võib olla kuni 30 märki.','username.regex'=>'Kasutajanimi peab algama @-märgiga ning sisaldama ainult tähti, numbreid, punkte ja alakriipse.',
        'password.string'=>'Uus parool peab olema tekst.','password.min'=>'Uus parool peab sisaldama vähemalt 8 märki.','password.max'=>'Uus parool võib olla kuni 255 märki.','password_confirm.required_with'=>'Kinnitage uus parool.','password_confirm.same'=>'Parooli kinnitus ei ühti.','old_password.string'=>'Praegune parool peab olema tekst.','password.required_with'=>'Sisestage uus parool.','oldPassword.string'=>'Praegune parool peab olema tekst.',
    ],
    'time_format'=>'Aeg peab olema kujul HH:MM.','end_after_start'=>'Lõpuaeg peab olema algusajast hilisem.',
    'fixed'=>['required'=>'Määrake vähemalt üks aeg.','array'=>'Ajavahemikud on vigases vormingus.','min'=>'Lisage vähemalt üks ajavahemik.','item_required'=>'Aeg ei tohi olla tühi.'],
    'masters'=>['required'=>'Valige vähemalt üks töötaja.','array'=>'Töötajate loend on vigases vormingus.','exists'=>'Valitud töötajat ei ole olemas.'],
    'appointment'=>['title'=>'Sisestage pealkiri.','name'=>'Sisestage eesnimi.','lastname'=>'Sisestage perekonnanimi.','phone'=>'Sisestage telefoninumber.','phone_format'=>'Kasutage rahvusvahelist vormingut, näiteks +372 55555555.','phone_min'=>'Telefon peab sisaldama vähemalt 8 märki.','description_min'=>'Kirjeldus peab sisaldama vähemalt 8 märki.','end_after'=>'Lõpuaeg peab olema algusajast hilisem.','service_required'=>'Valige teenus.','service_exists'=>'Valige teenus loendist.'],
    'days'=>['integer'=>'Päevade arv peab olema number.','required'=>'Sisestage päevade arv.'],
    'master_service'=>['price_required'=>'Sisestage individuaalne hind.','price_numeric'=>'Hind peab olema number.','price_min'=>'Hind ei tohi olla negatiivne.','duration_required'=>'Sisestage individuaalne kestus.','duration_min'=>'Kestus peab olema vähemalt üks minut.','minimum_required'=>'Sisestage individuaalne minimaalne kestus.','minimum_min'=>'Minimaalne kestus peab olema vähemalt üks minut.','buffer_before_required'=>'Sisestage teenusele eelnev puhver.','buffer_before_min'=>'Eelnev puhver ei tohi olla negatiivne.','buffer_after_required'=>'Sisestage teenusele järgnev puhver.','buffer_after_min'=>'Järgnev puhver ei tohi olla negatiivne.','service_mismatch'=>'Valitud teenus ei vasta saadetud vormile.','minimum_exceeds_duration'=>'Minimaalne kestus ei tohi ületada põhikestust.'],
    'site'=>['company_name'=>'Ettevõtte nimi on kohustuslik.','company_email'=>'Ettevõtte e-post on kohustuslik.','iframe'=>'Iframe on vigane.','facebook'=>'Facebooki URL on vigane.','youtube'=>'YouTube’i URL on vigane.','instagram'=>'Instagrami URL on vigane.','twitter'=>'Twitteri / X-i URL on vigane.'],
    'lunch_invalid'=>'Kontrollige lõunapausi aegu.',
];
