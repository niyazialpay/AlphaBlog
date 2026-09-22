<?php

return [

    'groups' => [
        'panel' => [
            'admin.*',
            'panel.*',
            'notifications.*',
            'chatbot',
            'chatbot.*',
            'cf.*',
            'adminRoutes',
            'adminRouteSave',
            'adminRoutesDelete',
            'adminRoutesShow',
            'lockscreen',
            'general.search',
            'manifest.panel',

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
