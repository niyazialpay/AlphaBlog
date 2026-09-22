<?php

namespace App\Support\Panel;

use Illuminate\Support\Facades\Lang;
use Nwidart\Modules\Facades\Module;
use Throwable;

final class PanelLang
{
    /**
     * @var list<string>
     */
    private const NAMESPACES = [
        'advertise', 'auth', 'cache', 'calendar', 'categories', 'chatbot', 'cloudflare',
        'comments', 'contact', 'contacts', 'crypt', 'dashboard', 'firewall', 'general',
        'ip_filter', 'language', 'logs', 'media', 'menu', 'messages', 'notes',
        'notifications', 'paginate', 'pagination', 'passwords', 'post', 'privacy',
        'profile', 'redirects', 'routes', 'search', 'sessions', 'settings', 'social',
        'tags', 'themes', 'user', 'webauthn',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function bag(?string $locale = null): array
    {
        $locale ??= session('language') ?? app()->getLocale();

        $bag = [];

        foreach (self::NAMESPACES as $namespace) {
            if (Lang::has($namespace, $locale)) {
                $bag[$namespace] = Lang::get($namespace, [], $locale);
            }
        }

        foreach (self::moduleNamespaces() as $namespace) {
            if (Lang::has($namespace, $locale)) {
                $bag[$namespace] = Lang::get($namespace, [], $locale);
            }
        }

        return $bag;
    }

    /**
     * @return list<string>
     */
    private static function moduleNamespaces(): array
    {
        if (! class_exists(Module::class)) {
            return [];
        }

        try {
            $modules = Module::allEnabled();
        } catch (Throwable) {
            return [];
        }

        $namespaces = [];

        foreach ($modules as $module) {
            $key = $module->getLowerName();
            $namespaces[] = $key.'::panel';
            $namespaces[] = $key.'::'.$key;

            foreach (self::moduleLangGroups($module) as $group) {
                $namespaces[] = $key.'::'.$group;
            }
        }

        return array_values(array_unique($namespaces));
    }

    /**
     * @return list<string>
     */
    private static function moduleLangGroups(\Nwidart\Modules\Module $module): array
    {
        $langPath = $module->getPath().'/lang';

        if (! is_dir($langPath)) {
            return [];
        }

        $groups = [];

        foreach (glob($langPath.'/*', GLOB_ONLYDIR) ?: [] as $localeDir) {
            foreach (glob($localeDir.'/*.php') ?: [] as $file) {
                $groups[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }

        return array_values(array_unique($groups));
    }
}
