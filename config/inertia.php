<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Server Side Rendering
    |--------------------------------------------------------------------------
    */

    'ssr' => [

        'enabled' => (bool) env('INERTIA_SSR_ENABLED', false),

        'url' => env('INERTIA_SSR_URL', 'http://127.0.0.1:13714'),

        'ensure_bundle_exists' => (bool) env('INERTIA_SSR_ENSURE_BUNDLE_EXISTS', false),

    ],

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    */

    'ensure_pages_exist' => false,

    'page_paths' => [
        resource_path('js/Pages'),

        // Panel sayfalari: cekirdek + her modulun kendi dizini.
        // Modul yoksa glob bos gecer.
        resource_path('js/panel/Pages'),
        ...glob(base_path('Modules/*/resources/js/panel/Pages')),
    ],

    'page_extensions' => [
        'js',
        'jsx',
        'svelte',
        'ts',
        'tsx',
        'vue',
    ],

    /*
    |--------------------------------------------------------------------------
    | Testing
    |--------------------------------------------------------------------------
    |
    | assertInertia()->component() bileseni GERCEKTEN var mi diye bakar ve bunun
    | icin AYRI bir view-finder kullanir. Panel sayfalari resources/js/Pages
    | altinda degil, bu yuzden yollar burada da bildirilmeli — aksi halde
    | "Inertia page component file [...] does not exist" alinir.
    |
    */

    'testing' => [

        'ensure_pages_exist' => true,

        'page_paths' => [
            resource_path('js/Pages'),
            resource_path('js/panel/Pages'),
            ...glob(base_path('Modules/*/resources/js/panel/Pages')),
        ],

        'page_extensions' => [
            'js',
            'jsx',
            'svelte',
            'ts',
            'tsx',
            'vue',
        ],

    ],

];
