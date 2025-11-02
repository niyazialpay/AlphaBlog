<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Theme Renderer
    |--------------------------------------------------------------------------
    |
    | Determines how frontend themes are rendered. Available options:
    |  - blade: render Blade views under resources/views/themes/{theme}
    |  - vue:   render Vue components via Inertia using resources/js/{theme}
    |
    */
    'renderer' => env('THEME_RENDERER', 'blade'),

    'vue' => [
        /*
        |--------------------------------------------------------------------------
        | Vue Theme Namespace
        |--------------------------------------------------------------------------
        |
        | Allows overriding the namespace used when resolving Vue components.
        | By default the active theme name from the database will be used.
        |
        */
        'theme_namespace' => env('THEME_VUE_NAMESPACE'),

        /*
        |--------------------------------------------------------------------------
        | Vue Page Root
        |--------------------------------------------------------------------------
        |
        | The directory under the theme namespace where page components live.
        |
        */
        'page_root' => env('THEME_VUE_PAGE_ROOT', 'Pages'),
    ],
];

