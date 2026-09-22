<?php

return [

    'subject' => env('VAPID_SUBJECT', env('APP_URL')),
    'public_key' => env('VAPID_PUBLIC_KEY'),
    'private_key' => env('VAPID_PRIVATE_KEY'),

    'prune_expired' => env('WEBPUSH_PRUNE_EXPIRED', true),

    'default_url' => env('WEBPUSH_DEFAULT_URL'),

];
