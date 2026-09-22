<?php

namespace App\Support\Panel;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

final class PanelResponse
{
    /**
     * @param  string  $component  Inertia bileşen adı (ör. "Posts/Index", "valefix::Customers/Index")
     * @param  string  $bladeView  Geri dönüş için eski Blade görünümü
     * @param  array<string, mixed>  $props  Vue sayfasının prop'ları
     * @param  array<string, mixed>|null  $viewData  Blade'in beklediği veri; null ise $props kullanılır
     */
    public static function render(string $component, string $bladeView, array $props = [], ?array $viewData = null): Response
    {
        if (! self::vueEnabled($component)) {
            return response()->view($bladeView, $viewData ?? $props);
        }

        return Inertia::render($component, $props)->toResponse(request());
    }

    /**
     * @template TValue
     *
     * @param  Paginator|CursorPaginator  $paginator
     * @param  callable(mixed, array-key): TValue  $callback
     */
    public static function rows(object $paginator, callable $callback): object
    {
        $projected = clone $paginator;
        $projected->setCollection($paginator->getCollection()->map($callback));

        return $projected;
    }

    private static function vueEnabled(string $component): bool
    {
        if (! Panel::vueEnabled($component)) {
            return false;
        }

        if (str_contains($component, '::') && ! self::modulePageExists($component)) {
            return false;
        }

        $routeName = request()->route()?->getName();

        return $routeName === null || PanelMenu::isInertia($routeName);
    }

    private static function modulePageExists(string $component): bool
    {
        [$namespace, $path] = explode('::', $component, 2);

        $needle = '/modules/'.strtolower($namespace).'/';

        foreach (glob(base_path('Modules/*/resources/js/panel/Pages/'.$path.'.vue')) ?: [] as $file) {
            if (str_contains(strtolower(str_replace('\\', '/', $file)), $needle)) {
                return true;
            }
        }

        return false;
    }
}
