<?php

return [

    /*
    |--------------------------------------------------------------------------
    | VAPID
    |--------------------------------------------------------------------------
    |
    | Tarayici push servisleri (FCM, Mozilla, Apple) gonderenin kimligini VAPID
    | ile dogruluyor. Anahtar cifti SITE BASINA uretilir ve DEGISMEZ: degisirse
    | mevcut tum abonelikler gecersiz olur ve kullanicilarin yeniden abone olmasi
    | gerekir.
    |
    | Uretmek icin:  php artisan webpush:vapid
    |
    | `subject` bir mailto: adresi ya da site URL'i olmali; push servisleri sorun
    | halinde buraya ulasiyor.
    |
    */

    'subject' => env('VAPID_SUBJECT', env('APP_URL')),
    'public_key' => env('VAPID_PUBLIC_KEY'),
    'private_key' => env('VAPID_PRIVATE_KEY'),

    /*
    | Abonelik bir push servisi tarafindan 404/410 ile reddedilirse cihaz
    | kaydi olmustur; satir otomatik silinir. Kapatmak icin false yapin.
    */
    'prune_expired' => env('WEBPUSH_PRUNE_EXPIRED', true),

    /*
    | Bildirim tiklandiginda acilacak varsayilan adres.
    */
    'default_url' => env('WEBPUSH_DEFAULT_URL'),

];
