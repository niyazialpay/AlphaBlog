<?php

/*
|--------------------------------------------------------------------------
| Ziggy
|--------------------------------------------------------------------------
|
| Panel kabugu `@routes('panel')` ile YALNIZCA panel route'larini basar.
| Ciplak `@routes` tum route tablosunu (her dil icin catch-all'lar dahil)
| gomer ve yuku gereksiz buyutur.
|
| ADMIN_PANEL_PATH env ile degisebildigi icin Vue tarafinda URL sabitlenemez;
| Ziggy derlenmis URI'leri serilestirdigi icin /admin ve /yonetim ayrimi
| otomatik dogru olur.
*/

return [

    'groups' => [
        'panel' => [
            'admin.*',          // routes/panel/**
            'panel.*',          // modul panel route'lari
            'notifications.*',
            'chatbot',
            'chatbot.*',
            'cf.*',             // Cloudflare
            'adminRoutes',
            'adminRouteSave',
            'adminRoutesDelete',
            'adminRoutesShow',
            'lockscreen',
            'general.search',
            'manifest.panel',

            // Panel prefix'i disindaki auth ekranlari
            'login',
            'login.first_step',
            'logout',
            'two-factor.*',
            'forgot-password',
            'password.*',
            'verification.*',
            'webauthn.*',
            'user.security.*',
            'user.session.*',
        ],
    ],

    'except' => [
        'debugbar.*',
        'horizon.*',
        'telescope.*',
        'pulse.*',
        'ignition.*',
        'log-viewer.*',
        'sanctum.*',
        'livewire.*',
    ],

];
