<?php

namespace App\Support\Panel;

use App\Models\User;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;
use Throwable;

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
            $section['order'] ??= $order + 0.1;
            $order += 0.1;

            $sections[] = $section;
        }

        return $sections;
    }

    /**
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

                return $tail === 'index' || Str::endsWith($tail, '.index') || ! Str::contains($tail, '.');
            })
            ->filter(fn (RoutingRoute $route) => self::routeAllows($route, $user))
            ->map(fn (RoutingRoute $route) => [
                'label' => self::itemLabel($key, (string) $route->getName(), $prefix),
                'icon' => 'fa-puzzle-piece',
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
