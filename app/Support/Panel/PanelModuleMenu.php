<?php

namespace App\Support\Panel;

use App\Models\User;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;
use Throwable;

/**
 * `@includeIf('<modül>::panel.menu')` ifadesinin Vue karşılığı.
 *
 * Blade'deki sözleşme "modül bir menü GÖRÜNÜMÜ yayınlıyorsa dahil et, yoksa
 * sessizce atla" idi. Vue'da markup paylaşılamaz, veri paylaşılır; sözleşme
 * aynı kalır:
 *
 *   1. Modules/<X>/config/panel_menu.php  → modülün yayın noktası (asıl mekanizma)
 *   2. config/panel_menu.local.php        → site sahibinin elle override'ı (gitignore'lu)
 *   3. Route tablosundan otomatik keşif   → henüz (1)'i yazılmamış modüller için yedek
 *
 * Katman 3 sayesinde HİÇ değiştirilmemiş bir modül de yeni sidebar'da görünür.
 * Bir öğenin Inertia ile mi açılacağı config/panel_inertia_routes.php defterinden
 * türetilir (PanelMenu::isInertia) — modül ekranı taşınana dek tam sayfa yüklemesi.
 */
final class PanelModuleMenu
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function build(?User $user): array
    {
        if (! class_exists(Module::class)) {
            return [];
        }

        try {
            $modules = Module::allEnabled();
        } catch (Throwable) {
            return [];
        }

        $local = self::localOverrides();
        $sections = [];
        $order = 31;

        foreach ($modules as $module) {
            $key = $module->getLowerName();

            $section = self::fromModuleConfig($module)
                ?? ($local[$key] ?? null)
                ?? self::fromRouteTable($key, $user);

            if ($section === null || ($section['items'] ?? []) === []) {
                continue;
            }

            $section['key'] ??= $key;
            $section['label'] ??= self::sectionLabel($key, $module->getName());
            $section['icon'] ??= 'fa-cube';
            // Modüller blade'de de notlardan sonra, yönetimden önce geliyordu (30 < x < 40).
            $section['order'] ??= $order + 0.1;
            $order += 0.1;

            $sections[] = $section;
        }

        return $sections;
    }

    /**
     * Katman 1 — modülün kendi yayını.
     *
     * @return array<string, mixed>|null
     */
    private static function fromModuleConfig(object $module): ?array
    {
        $path = $module->getPath().'/config/panel_menu.php';

        if (! is_file($path)) {
            return null;
        }

        $section = require $path;

        return is_array($section) ? $section : null;
    }

    /**
     * Katman 2 — site başına elle override.
     *
     * @return array<string, array<string, mixed>>
     */
    private static function localOverrides(): array
    {
        $path = config_path('panel_menu.local.php');

        if (! is_file($path)) {
            return [];
        }

        $overrides = require $path;

        return is_array($overrides) ? $overrides : [];
    }

    /**
     * Katman 3 — route tablosundan otomatik keşif.
     *
     * Her modül panel route'unu `panel.<lowername>.*` adıyla kaydediyor.
     * Yetki, route üzerindeki `->can(...)` çağrısının ürettiği `can:ability,Model`
     * middleware string'i parse edilerek değerlendirilir; böylece görünürlük
     * modülün kendi @can sarmalayıcılarıyla eşleşir, modül blade'i okunmadan.
     *
     * @return array<string, mixed>|null
     */
    private static function fromRouteTable(string $key, ?User $user): ?array
    {
        $prefix = 'panel.'.$key.'.';

        $items = collect(Route::getRoutes()->getRoutes())
            ->filter(fn (RoutingRoute $route) => Str::startsWith((string) $route->getName(), $prefix))
            ->filter(fn (RoutingRoute $route) => in_array('GET', $route->methods(), true))
            ->filter(fn (RoutingRoute $route) => ! Str::contains($route->uri(), '{'))
            ->filter(function (RoutingRoute $route) use ($prefix) {
                $tail = Str::after((string) $route->getName(), $prefix);

                // Yalnızca üst seviye giriş noktaları: "customers.index" veya "home".
                return $tail === 'index' || Str::endsWith($tail, '.index') || ! Str::contains($tail, '.');
            })
            ->filter(fn (RoutingRoute $route) => self::routeAllows($route, $user))
            ->map(fn (RoutingRoute $route) => [
                'label' => self::itemLabel($key, (string) $route->getName(), $prefix),
                'icon' => 'fa-circle-dot',
                'url' => url($route->uri()),
                'route' => $route->getName(),
                'active' => Str::after($route->uri(), Panel::path().'/'),
            ])
            ->values()
            ->all();

        return $items === [] ? null : ['items' => $items];
    }

    private static function routeAllows(RoutingRoute $route, ?User $user): bool
    {
        foreach ($route->gatherMiddleware() as $middleware) {
            if (! is_string($middleware) || ! Str::startsWith($middleware, 'can:')) {
                continue;
            }

            if ($user === null) {
                return false;
            }

            $arguments = explode(',', Str::after($middleware, 'can:'));
            $ability = array_shift($arguments);

            // Model bağlamalı kapılar (ör. can:edit,post) menüde değerlendirilemez;
            // parametreli route'lar zaten elenmiş oluyor, yine de güvenli tarafta kal.
            try {
                if (! Gate::forUser($user)->allows($ability, $arguments === [] ? null : $arguments)) {
                    return false;
                }
            } catch (Throwable) {
                return false;
            }
        }

        return true;
    }

    private static function sectionLabel(string $key, string $name): string
    {
        foreach (["{$key}::panel.menu.title", "{$key}::{$key}.menu.title", "{$key}::panel.title"] as $candidate) {
            if (($translated = PanelMenu::translate($candidate)) !== null) {
                return $translated;
            }
        }

        return $name;
    }

    private static function itemLabel(string $key, string $routeName, string $prefix): string
    {
        $segment = Str::before(Str::after($routeName, $prefix), '.');

        foreach ([
            "{$key}::panel.menu.{$segment}",
            "{$key}::{$key}.menu.{$segment}",
            "{$key}::panel.{$segment}",
            "{$key}::{$key}.{$segment}",
        ] as $candidate) {
            if (($translated = PanelMenu::translate($candidate)) !== null) {
                return $translated;
            }
        }

        return Str::headline($segment);
    }
}
