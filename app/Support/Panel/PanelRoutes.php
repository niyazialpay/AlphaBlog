<?php

namespace App\Support\Panel;

use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

final class PanelRoutes
{
    /**
     * @return array<string, mixed>
     */
    public static function payload(string $group = 'panel'): array
    {
        return [
            'url' => rtrim((string) config('app.url'), '/'),
            'port' => parse_url((string) config('app.url'), PHP_URL_PORT),
            'defaults' => (object) [],
            'routes' => self::routes($group),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function routes(string $group): array
    {
        $only = (array) config('ziggy.groups.'.$group, []);
        $except = (array) config('ziggy.except', []);
        $out = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            $name = $route->getName();

            if ($name === null) {
                continue;
            }

            if ($only !== [] && ! Str::is($only, $name)) {
                continue;
            }

            if ($except !== [] && Str::is($except, $name)) {
                continue;
            }

            $entry = [
                'uri' => $route->uri(),
                'methods' => array_values(array_diff($route->methods(), ['HEAD'])),
            ];

            $parameters = $route->parameterNames();

            if ($parameters !== []) {
                $entry['parameters'] = $parameters;
            }

            $bindings = self::bindings($route);

            if ($bindings !== []) {
                $entry['bindings'] = $bindings;
            }

            $wheres = array_intersect_key($route->wheres, array_flip($parameters));

            if ($wheres !== []) {
                $entry['wheres'] = $wheres;
            }

            if ($domain = $route->getDomain()) {
                $entry['domain'] = $domain;
            }

            $out[$name] = $entry;
        }

        ksort($out);

        return $out;
    }

    /**
     * @return array<string, string>
     */
    private static function bindings(RoutingRoute $route): array
    {
        $bindings = [];

        foreach ($route->bindingFields() as $parameter => $field) {
            $bindings[$parameter] = $field;
        }

        return $bindings;
    }
}
