<?php

namespace Tests\Feature\Panel;

use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;

/**
 * "Route adları ve URI'ler değişmeyecek" garantisinin testi.
 *
 * Migrasyonun tek sert kuralı buydu: panel Vue'ya taşınırken hiçbir route adı,
 * URI'si, HTTP verb'ü ya da `can:` kapısı değişmeyecek — aksi halde kayıtlı
 * bağlantılar, modül menüleri ve harici entegrasyonlar sessizce kırılır.
 *
 * Fixture (`tests/Fixtures/panel-routes.json`) commit'li bir anlık görüntüdür.
 * Kasıtlı bir değişiklik olduğunda fixture ile birlikte güncellenir; kazara
 * değişiklik testi kırar.
 *
 * Kapsam ÇEKİRDEK panel prefix'i: modül route'ları modül başına ayrı yaşıyor ve
 * bu repoda izlenmiyor (`/Modules` gitignore'lu), fixture'a girmeleri sahte bir
 * kırılganlık yaratırdı.
 */
class PanelRouteContractTest extends PanelTestCase
{
    private const FIXTURE = 'tests/Fixtures/panel-routes.json';

    /** Modül prefix'leri — bunlar fixture dışı. */
    private const MODULE_SEGMENTS = [
        'valefix', 'cihansk', 'birderakademi', 'birdergi', 'edergi', 'podcast', 'xsayfa',
    ];

    public function test_core_panel_routes_match_committed_snapshot(): void
    {
        $actual = self::snapshot();
        $path = base_path(self::FIXTURE);

        if (! is_file($path)) {
            // Ilk calistirmada fixture'i uret; commit edilmesi gerekir.
            @mkdir(dirname($path), 0777, true);
            file_put_contents($path, json_encode($actual, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n");

            $this->markTestSkipped('Fixture uretildi: '.self::FIXTURE.' — commit edin ve testi tekrar calistirin.');
        }

        $expected = json_decode((string) file_get_contents($path), true);

        $this->assertSame(
            $expected,
            $actual,
            'Panel route sozlesmesi degisti. Degisiklik KASITLI ise '.self::FIXTURE
            .' dosyasini silip testi tekrar calistirarak fixture\'i yeniden uretin.'
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function snapshot(): array
    {
        $prefix = trim((string) config('settings.admin_panel_path', 'admin'), '/');
        $rows = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            $uri = $route->uri();

            if (! str_starts_with($uri, $prefix.'/') && $uri !== $prefix) {
                continue;
            }

            $tail = ltrim(substr($uri, strlen($prefix)), '/');
            $head = explode('/', $tail)[0] ?? '';

            if (in_array($head, self::MODULE_SEGMENTS, true)) {
                continue;
            }

            $name = $route->getName();

            if ($name === null) {
                continue;
            }

            $rows[$name] = [
                'uri' => $uri,
                'methods' => array_values(array_diff($route->methods(), ['HEAD'])),
                'gates' => self::gates($route),
            ];
        }

        ksort($rows);

        return $rows;
    }

    /**
     * `->can('admin', User::class)` middleware'e `can:admin,App\Models\User`
     * olarak yaziliyor; yetki kapisi da sozlesmenin parcasi.
     *
     * @return list<string>
     */
    private static function gates(RoutingRoute $route): array
    {
        $gates = [];

        foreach ($route->gatherMiddleware() as $middleware) {
            if (is_string($middleware) && str_starts_with($middleware, 'can:')) {
                $gates[] = $middleware;
            }
        }

        sort($gates);

        return $gates;
    }
}
