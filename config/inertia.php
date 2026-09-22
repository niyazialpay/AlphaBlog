<?php

return [

    'ssr' => [

        'enabled' => (bool) env('INERTIA_SSR_ENABLED', false),

        'url' => env('INERTIA_SSR_URL', 'http://127.0.0.1:13714'),

        'ensure_bundle_exists' => (bool) env('INERTIA_SSR_ENSURE_BUNDLE_EXISTS', false),

    ],

    'ensure_pages_exist' => false,

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
