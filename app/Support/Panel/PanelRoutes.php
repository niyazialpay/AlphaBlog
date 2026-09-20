<?php

namespace App\Support\Panel;

use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * `window.Ziggy` yükünü Laravel'in KENDİ route tablosundan üretir.
 *
 * NEDEN VAR: kök blade `@routes('panel')` kullanıyordu. Bu bir Blade
 * DİREKTİFİ ve direktifi `tightenco/ziggy` paketinin service provider'ı
 * kaydediyor. Paket o ortamda yüklü değilse Blade tanımadığı direktifi
 * OLDUĞU GİBİ metin olarak basar — sayfada düz `@routes('panel')` yazısı
 * görünür, `window.Ziggy` hiç tanımlanmaz, `route()` ilk çağrıda fırlatır ve
 * panel bomboş açılır. Teşhisi zor, tek semptomu boş ekran.
 *
 * Bu sınıf aynı yükü composer paketine ihtiyaç duymadan üretir. `ziggy-js`
 * (npm tarafı, bundle'a zaten giriyor) bu şekli aynen okur, dolayısıyla
 * istemcide hiçbir şey değişmez.
 *
 * Ziggy PHP paketi yüklüyse ONA bırakılır (filtre/binding semantiğinin
 * birebir aynısı); yüklü değilse burası devreye girer. Yani çalışan
 * kurulumlarda davranış aynı, çalışmayanlarda panel yine açılır.
 */
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

            /*
             * Grup seviyesinde tanimlanan `where` kurallari o gruptaki TUM
             * route'lara yapisiyor — parametresi olmayanlara bile. Yalnizca
             * URI'de gercekten bulunan parametrelerin kurali tasinir.
             */
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
     * Route-model binding alan adları: `/{post:slug}` → ['post' => 'slug'].
     *
     * ziggy-js bunu `route('x', $model)` çağrılarında hangi alanın URL'e
     * gireceğini seçmek için kullanıyor.
     *
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
