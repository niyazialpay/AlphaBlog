<?php

return [

    'relying_party' => [
        'name' => env('WEBAUTHN_NAME', config('app.name')),
        'id' => env('WEBAUTHN_ID'),
    ],

    'challenge' => [
        'bytes' => 16,
        'timeout' => 60,
        'key' => '_webauthn',
    ],
];
