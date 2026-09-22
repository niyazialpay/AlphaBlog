<?php

use App\Models\Post\Categories;
use App\Models\Post\Comments;
use App\Models\Post\Posts;
use App\Models\User;

return [

    [
        'key' => 'genel',
        'label' => 'dashboard.dashboard',
        'icon' => 'fa-gauge-high',
        'order' => 10,
        'items' => [
            ['label' => 'dashboard.dashboard', 'icon' => 'fa-gauge-high', 'route' => 'admin.index', 'active' => ''],
            ['label' => 'dashboard.analytics', 'icon' => 'fa-chart-mixed', 'style' => 'regular', 'route' => 'admin.analytics', 'active' => 'analytics', 'can' => ['admin', User::class]],
            ['label' => 'Search Console', 'icon' => 'fa-magnifying-glass-chart', 'route' => 'admin.search-console', 'active' => 'search-console', 'can' => ['admin', User::class]],
            ['label' => 'chatbot.chatbot', 'icon' => 'fa-robot', 'route' => 'chatbot', 'active' => 'ai-chatbot*', 'when' => 'ai'],
        ],
    ],

    [
        'key' => 'icerik',
        'label' => 'post.blog',
        'icon' => 'fa-file-lines',
        'order' => 20,
        'items' => [
            ['label' => 'general.new', 'icon' => 'fa-file', 'route' => 'admin.post.create', 'params' => ['type' => 'blogs'], 'active' => 'blogs/create', 'can' => ['createPost', Posts::class]],
            ['label' => 'post.all_blogs', 'icon' => 'fa-file-lines', 'route' => 'admin.posts', 'params' => ['type' => 'blogs', 'language' => 'defaultLanguage'], 'active' => ['blogs', 'blogs/*/edit']],
            ['label' => 'categories.categories', 'icon' => 'fa-list', 'route' => 'admin.categories', 'active' => 'blogs/categories*', 'can' => ['category', Categories::class]],
            ['label' => 'comments.comments', 'icon' => 'fa-comments', 'route' => 'admin.post.comments', 'active' => 'blogs/comments*', 'can' => ['view', Comments::class], 'badge' => 'newComments'],
            ['label' => 'search.searched_words', 'icon' => 'fa-file-magnifying-glass', 'route' => 'admin.search.index', 'active' => 'search*', 'can' => ['createPost', Posts::class], 'badge' => 'searchedWords'],
            ['label' => 'post.pages', 'icon' => 'fa-file', 'route' => 'admin.posts', 'params' => ['type' => 'pages', 'language' => 'defaultLanguage'], 'active' => ['pages', 'pages/*/edit'], 'can' => ['viewPages', Posts::class]],
            ['label' => 'contact.messages', 'icon' => 'fa-inbox', 'route' => 'admin.contact_messages', 'active' => 'contact/messages*', 'can' => ['admin', User::class]],
            ['label' => 'contact.contact_page', 'icon' => 'fa-message-text', 'route' => 'admin.contact_page', 'active' => 'contact', 'can' => ['admin', User::class]],
        ],
    ],

    [
        'key' => 'notlar',
        'label' => 'notes.notes',
        'icon' => 'fa-file-shield',
        'order' => 30,
        'items' => [
            ['label' => 'general.new', 'icon' => 'fa-file', 'route' => 'admin.notes.create', 'active' => 'notes/create'],
            ['label' => 'notes.all_notes', 'icon' => 'fa-file-shield', 'route' => 'admin.notes', 'active' => ['notes', 'notes/show/*']],
            ['label' => 'categories.categories', 'icon' => 'fa-list', 'route' => 'admin.notes.categories', 'active' => 'notes/categories*'],
        ],
    ],

    [
        'key' => 'yonetim',
        'label' => 'settings.management',
        'icon' => 'fa-gear',
        'order' => 40,
        'can' => ['admin', User::class],
        'items' => [
            ['label' => 'redirects.redirects', 'icon' => 'fa-route', 'route' => 'adminRoutes', 'active' => 'routes*'],
            ['label' => 'user.users', 'icon' => 'fa-user', 'route' => 'admin.users', 'active' => 'users*'],
            ['label' => 'menu.menu', 'icon' => 'fa-bars', 'route' => 'admin.menu.index', 'active' => 'menu*'],
            ['label' => 'settings.settings', 'icon' => 'fa-gear', 'route' => 'admin.settings', 'active' => 'settings*'],
            ['label' => 'dashboard.dashboard', 'icon' => 'fa-cloudflare', 'style' => 'brands', 'route' => 'cf.dashboard', 'active' => 'cloudflare', 'can' => ['cloudflare', User::class], 'group' => 'Cloudflare'],
            ['label' => 'DNS', 'icon' => 'fa-globe', 'route' => 'cf.dns', 'active' => 'cloudflare/dns', 'can' => ['cloudflare', User::class], 'group' => 'Cloudflare'],
            ['label' => 'cache.clear_cache', 'icon' => 'fa-broom', 'action' => 'clear-cache', 'can' => ['admin', User::class]],
            ['label' => 'notifications.notifications', 'icon' => 'fa-bell', 'route' => 'notifications.index', 'active' => 'notifications*', 'badge' => 'unreadNotifications'],
            ['label' => 'user.profile', 'icon' => 'fa-id-badge', 'route' => 'admin.profile.index', 'active' => 'profile*'],
        ],
    ],

    [
        'key' => 'guvenlik',
        'label' => 'firewall.security',
        'icon' => 'fa-shield-halved',
        'order' => 50,
        'can' => ['admin', User::class],
        'items' => [
            ['label' => 'ip_filter.ip_filter', 'icon' => 'fa-shield-halved', 'route' => 'admin.ip-filter', 'active' => 'ip-filter*'],
            ['label' => 'firewall.rules', 'icon' => 'fa-user-secret', 'route' => 'admin.firewall', 'active' => 'firewall'],
            ['label' => 'firewall.logs', 'icon' => 'fa-clipboard-list', 'route' => 'admin.firewall.logs', 'active' => 'firewall/logs'],
            ['label' => 'logs.logs', 'icon' => 'fa-clipboard-list', 'route' => 'admin.system-logs', 'active' => 'system-logs'],
        ],
    ],

    [
        'key' => 'sistem',
        'label' => 'settings.monitoring',
        'icon' => 'fa-heart-pulse',
        'order' => 60,
        'can' => ['admin', User::class],
        'items' => [
            ['label' => 'Pulse', 'icon' => 'fa-heart-pulse', 'route' => 'admin.monitoring.pulse', 'active' => 'monitoring/pulse'],
            ['label' => 'Telescope', 'icon' => 'fa-binoculars', 'route' => 'admin.monitoring.telescope', 'active' => 'monitoring/telescope'],
            ['label' => 'Horizon', 'icon' => 'fa-laravel', 'style' => 'brands', 'route' => 'admin.monitoring.horizon', 'active' => 'monitoring/horizon'],
            ['label' => 'logs.logs', 'icon' => 'fa-clipboard-list', 'route' => 'admin.monitoring.logs', 'active' => 'monitoring/logs'],
            ['label' => 'general.about', 'icon' => 'fa-address-card', 'route' => 'admin.about', 'active' => 'about'],
        ],
    ],

];
