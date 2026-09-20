<?php

namespace App\Support\Panel;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Throwable;

/**
 * Sidebar menüsünü veri olarak üretir.
 *
 * Çekirdek bölümler `config/panel_menu.php`'den, modül bölümleri
 * {@see PanelModuleMenu}'den gelir; ikisi `order` ile harmanlanır.
 *
 * Rozetler `View::shared()` üzerinden okunur — sayımları NewCommentsCount ve
 * SearchedWords middleware'leri zaten yapıyor. Böylece ne ikinci bir COUNT(*)
 * sorgusu atılır ne de eski AdminLTE kabuğuyla sayı farkı oluşur.
 */
final class PanelMenu
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function build(?User $user): array
    {
        $sections = array_merge(
            (array) config('panel_menu', []),
            PanelModuleMenu::build($user),
        );

        $built = [];

        foreach ($sections as $section) {
            if (! self::allows($section['can'] ?? null, $user)) {
                continue;
            }

            $items = [];

            foreach ($section['items'] ?? [] as $item) {
                $resolved = self::item($item, $user);

                if ($resolved !== null) {
                    $items[] = $resolved;
                }
            }

            if ($items === []) {
                continue;
            }

            $built[] = [
                'key' => $section['key'],
                'label' => self::label($section['label'] ?? $section['key']),
                'icon' => self::icon($section['icon'] ?? 'fa-circle-dot', $section['style'] ?? null),
                'order' => $section['order'] ?? 100,
                'items' => $items,
            ];
        }

        usort($built, fn (array $a, array $b) => $a['order'] <=> $b['order']);

        return $built;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>|null
     */
    private static function item(array $item, ?User $user): ?array
    {
        if (! self::allows($item['can'] ?? null, $user)) {
            return null;
        }

        if (($item['when'] ?? null) === 'ai' && ! self::aiEnabled()) {
            return null;
        }

        $url = $item['url'] ?? null;

        if ($url === null && isset($item['route'])) {
            $url = self::url($item['route'], $item['params'] ?? []);

            // Route çözülemiyorsa (devre dışı modül, kaldırılmış ekran) öğeyi gösterme.
            if ($url === null) {
                return null;
            }
        }

        return [
            'label' => self::label($item['label']),
            'icon' => self::icon($item['icon'] ?? 'fa-circle-dot', $item['style'] ?? null),
            'url' => $url,
            'route' => $item['route'] ?? null,
            'action' => $item['action'] ?? null,
            'group' => $item['group'] ?? null,
            'badge' => self::badge($item['badge'] ?? null, $item['badge_count'] ?? null),
            'active' => self::isActive($item['active'] ?? null),
            'inertia' => $item['inertia'] ?? self::isInertia($item['route'] ?? null),
        ];
    }

    /**
     * @param  array<int, mixed>|null  $can
     */
    private static function allows(?array $can, ?User $user): bool
    {
        if ($can === null) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        [$ability, $model] = $can + [1 => null];

        try {
            return Gate::forUser($user)->allows($ability, $model);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private static function url(string $name, array $params): ?string
    {
        if (! app('router')->has($name)) {
            return null;
        }

        foreach ($params as $key => $value) {
            if ($value === 'defaultLanguage') {
                $params[$key] = self::defaultLanguage();
            }
        }

        try {
            return route($name, $params);
        } catch (Throwable) {
            return null;
        }
    }

    private static function defaultLanguage(): ?string
    {
        try {
            return app('default_language')?->code;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Çeviri anahtarı ise çevir, değilse ham metin olarak bırak
     * (blade'de "Search Console", "DNS", "Pulse" gibi sabitler de var).
     */
    public static function label(string $label): string
    {
        return self::translate($label) ?? $label;
    }

    /**
     * Ceviriyi YALNIZCA string ise dondurur.
     *
     * Lang::has() ic ice diziler icin de true doner (modul panel.php dosyalari
     * cogunlukla ekran basina gruplanmis dizilerdir) ve __() o zaman dizi dondurur.
     * Bu guard olmadan menu uretimi TypeError ile 500 veriyordu.
     */
    public static function translate(string $key): ?string
    {
        if (! Lang::has($key)) {
            return null;
        }

        $value = __($key);

        return is_string($value) ? $value : null;
    }

    public static function icon(string $icon, ?string $style = null): string
    {
        if ($style === 'brands') {
            return 'fa-brands '.$icon;
        }

        $prefix = config('settings.fontawesome_pro')
            ? 'fa-duotone'
            : 'fa-'.($style ?? 'solid');

        return $prefix.' '.$icon;
    }

    /**
     * Rozet sayacı.
     *
     * Çekirdek anahtarları (`newComments`, `searchedWords`,
     * `unreadNotifications`) mevcut `View::share` değerlerinden okunur — çift
     * `COUNT(*)` yok, eski AdminLTE kabuğuyla daima tutarlı.
     *
     * Modüller çekirdeğe dokunmadan kendi sayaçlarını verebilsin diye ikinci bir
     * yol var: `panel_menu.php` içinde `'badge_count' => [Sinif::class, 'metot']`.
     * Dizi biçimli callable bilinçli: `config:cache` Closure'ları serileştiremez,
     * dizi callable'ı sorunsuz taşır. Sayaç patlarsa rozet gösterilmez — bir
     * modülün hatası tüm sidebar'ı düşürmemeli.
     *
     * @param  array{0: class-string, 1: string}|string|null  $counter
     */
    private static function badge(?string $key, array|string|null $counter = null): ?int
    {
        $count = match ($key) {
            'newComments' => (int) View::shared('newCommentsCount', 0),
            'searchedWords' => (int) View::shared('searchedWordsCount', 0),
            'unreadNotifications' => (int) (auth()->user()?->unreadNotifications()->count() ?? 0),
            default => 0,
        };

        if ($count === 0 && $counter !== null && is_callable($counter)) {
            try {
                $count = (int) $counter();
            } catch (Throwable) {
                $count = 0;
            }
        }

        return $count > 0 ? $count : null;
    }

    /**
     * @param  string|list<string>|null  $patterns
     */
    private static function isActive(string|array|null $patterns): bool
    {
        if ($patterns === null) {
            return false;
        }

        $path = Panel::path();

        foreach ((array) $patterns as $pattern) {
            $full = $pattern === '' ? $path : $path.'/'.ltrim($pattern, '/');

            if (request()->is($full)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Route Vue'ya taşındı mı? Tek defter: config/panel_inertia_routes.php.
     *
     * Taşınmamış ekrana `router.visit()` yapılamaz — yanıtta X-Inertia yok,
     * client "plain JSON/HTML response" hatası verir. O yüzden varsayılan false.
     */
    public static function isInertia(?string $routeName): bool
    {
        if ($routeName === null) {
            return false;
        }

        $patterns = (array) config('panel_inertia_routes', []);

        return $patterns !== [] && Str::is($patterns, $routeName);
    }

    /**
     * İstemci tarafı için aynı defter (komut paleti, bildirim zili, çapraz bağlantılar).
     *
     * @return list<string>
     */
    public static function inertiaRoutePatterns(): array
    {
        return array_values((array) config('panel_inertia_routes', []));
    }

    public static function aiEnabled(): bool
    {
        return collect(config('ai.providers', []))
            ->contains(fn ($provider) => filled($provider['key'] ?? null));
    }

    /**
     * Modül route adlarından okunabilir etiket türetir (katman 3 yedeği).
     */
    public static function headline(string $routeName): string
    {
        return Str::headline(Str::afterLast($routeName, '.'));
    }
}
