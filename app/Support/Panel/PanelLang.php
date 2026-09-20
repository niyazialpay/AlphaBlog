<?php

namespace App\Support\Panel;

use Illuminate\Support\Facades\Lang;
use Nwidart\Modules\Facades\Module;
use Throwable;

/**
 * Panelin çeviri torbası.
 *
 * Yeni bir `panel.php` namespace'i AÇILMAZ: panel metinleri bugün ~30 mevcut
 * namespace'e dağılmış durumda (general, post, settings, firewall, …) ve eski
 * Blade ekranları hâlâ onları kullanıyor. İkinci bir kaynak yaratmak kaçınılmaz
 * olarak drift üretirdi.
 *
 * Torba Inertia paylaşılan prop'u DEĞİLDİR — kök blade'de `window.__panelLang`
 * olarak bir kez basılır. Paylaşılan prop olsaydı her Inertia ziyaretinde
 * yeniden serileştirilirdi; böyle tam sayfa başına bir kez ödenir, ziyaret
 * başına sıfır.
 */
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
     * Etkin modüllerin panel çevirileri: `valefix::panel`, `birdergi::birdergi`, …
     * Lang dizini olmayan modüller (ör. XSayfaMuhasebe) Lang::has ile elenir.
     *
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
     * Modülün lang/{locale} dizinlerinde fiilen var olan dosya adlarını (grup
     * adlarını) döner. `panel` ve modül anahtarı yukarıda zaten eklendiği için
     * burada tekrar gelmesi zararsızdır (array_unique ile sadeleşir). Böylece
     * `valefix::ai` gibi ikinci bir dosyası olan her modül, elle listeye
     * eklenmeye gerek kalmadan torbaya girer.
     *
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
