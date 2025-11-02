<?php

namespace App\Support;

use Illuminate\Support\Str;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ThemeManager
{
    /**
     * Render the requested theme view using Blade or Vue (Inertia).
     */
    public static function render(string $view, array $data = [], int $status = 200, array $headers = []): SymfonyResponse
    {
        if (self::usingVue()) {
            $component = self::componentName($view);

            $structuredData = ThemeData::baseStructuredData();

            $pageStructuredData = $data['structuredData'] ?? null;

            if (! $pageStructuredData && isset($data['post']['structuredData'])) {
                $pageStructuredData = $data['post']['structuredData'];
                unset($data['post']['structuredData']);
            }

            if ($pageStructuredData && is_array($pageStructuredData)) {
                $structuredData = array_merge($structuredData, $pageStructuredData);
            }

            $data['structuredData'] = $structuredData;

            $response = Inertia::render($component, $data)
                ->withViewData('structuredData', $structuredData)
                ->toResponse(request());
            $response->setStatusCode($status);

            foreach ($headers as $key => $value) {
                $response->headers->set($key, $value);
            }

            return $response;
        }

        $bladeView = self::resolveBladeView($view);

        return response()->view($bladeView, $data, $status, $headers);
    }

    /**
     * Determine if the Vue renderer is enabled.
     */
    public static function usingVue(): bool
    {
        return config('theme.renderer') === 'vue';
    }

    /**
     * Resolve the component name that Inertia should render.
     */
    protected static function componentName(string $view): string
    {
        $themeNamespace = config('theme.vue.theme_namespace')
            ?: (app()->bound('theme') ? (string) app('theme')->name : 'Default');

        $pageRoot = trim(config('theme.vue.page_root', 'Pages'), '/');

        $segments = collect(explode('.', $view))
            ->map(fn (string $segment): string => Str::studly($segment))
            ->implode('/');

        return collect([$themeNamespace, $pageRoot, $segments])
            ->filter()
            ->implode('/');
    }

    /**
     * Resolve the Blade view path with theme fallback.
     */
    protected static function resolveBladeView(string $view): string
    {
        $themeName = app()->bound('theme') ? (string) app('theme')->name : 'Default';
        $themedView = 'themes.'.$themeName.'.'.$view;

        if (view()->exists($themedView)) {
            return $themedView;
        }

        $fallbackView = 'Default.'.$view;
        if (view()->exists($fallbackView)) {
            return $fallbackView;
        }

        return $view;
    }
}
