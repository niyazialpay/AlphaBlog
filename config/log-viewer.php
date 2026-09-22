<?php

use Opcodes\LogViewer\Http\Middleware\AuthorizeLogViewer;
use Opcodes\LogViewer\Http\Middleware\EnsureFrontendRequestsAreStateful;

return [

    'enabled' => env('LOG_VIEWER_ENABLED', true),

    'api_only' => env('LOG_VIEWER_API_ONLY', false),

    'require_auth_in_production' => true,

    'route_domain' => null,

    'route_path' => env('LOG_VIEWER_ROUTE_PATH', 'log-viewer'),

    'back_to_system_url' => null,

    'back_to_system_label' => null,

    'timezone' => null,

    'middleware' => [
        'web',
        AuthorizeLogViewer::class,
    ],

    'api_middleware' => [
        EnsureFrontendRequestsAreStateful::class,
        AuthorizeLogViewer::class,
    ],

    'api_stateful_domains' => env('LOG_VIEWER_API_STATEFUL_DOMAINS') ? explode(',', env('LOG_VIEWER_API_STATEFUL_DOMAINS')) : null,

    'hosts' => [
        'local' => [
            'name' => ucfirst(env('APP_ENV', 'local')),
        ],

    ],

    'include_files' => [
        '*.log',
        '**/*.log',

        '/var/log/httpd/*',
        '/var/log/apache2/*',
        '/var/log/nginx/*',

        '/opt/homebrew/var/log/nginx/*',
        '/opt/homebrew/var/log/httpd/*',
        '/opt/homebrew/var/log/php-fpm.log',
        '/opt/homebrew/var/log/postgres*log',
        '/opt/homebrew/var/log/redis*log',
        '/opt/homebrew/var/log/supervisor*log',

        env('LOG_PATH'),
        '~/logs/*',
        '~/logs/*/*',
    ],

    'exclude_files' => [
    ],

    'hide_unknown_files' => true,

    'shorter_stack_trace_excludes' => [
        '/vendor/symfony/',
        '/vendor/laravel/framework/',
        '/vendor/barryvdh/laravel-debugbar/',
    ],

    'cache_driver' => env('LOG_VIEWER_CACHE_DRIVER', null),

    'lazy_scan_chunk_size_in_mb' => 50,

    'strip_extracted_context' => true,
];
