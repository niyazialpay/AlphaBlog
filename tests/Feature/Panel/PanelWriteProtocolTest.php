<?php

namespace Tests\Feature\Panel;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class PanelWriteProtocolTest extends PanelTestCase
{
    /**
     * @var list<string>
     */
    private const SKIP = [
        'admin.logout',
        'user.session.logout',
        'user.session.logout-all',
        'admin.user.secret-login.post',

        'admin.cloudflare.cache.clear',
        'admin.cloudflare.toggle.development',
        'cf.dns.save',
        'cf.dns.delete',
        'cf.update.api.settings',
        'admin.analytics.fetch',
        'admin.search-console.fetch',
        'chatbot.message',

        'webauthn.register.options',
        'webauthn.register',
        'user.security.webauthn',

        'two-factor.enable',
        'two-factor.confirm',
        'two-factor.disable',
    ];

    /**
     * @var list<string>
     */
    private const MODULE_SEGMENTS = [
        'valefix', 'cihansk', 'birderakademi', 'birdergi', 'edergi', 'podcast', 'xsayfa',
    ];

    public function test_inertia_called_write_endpoints_speak_inertia(): void
    {
        $inertiaCalled = self::routeNamesCalledWith('router|useForm');

        $this->assertNotEmpty(
            $inertiaCalled,
            'Vue kaynagindan tek bir Inertia cagrisi cikarilamadi — tarama bozuk, '
            .'test bu haliyle hicbir sey dogrulamaz.'
        );

        $failures = [];

        foreach ($this->panelWriteRoutes() as $name => $method) {
            if (! in_array($name, $inertiaCalled, true)) {
                continue;
            }

            $this->migrateScreens([$name]);

            try {
                $response = $this->actingAs($this->owner)->call(
                    $method,
                    route($name),
                    [],
                    [],
                    [],
                    $this->transformHeadersToServerVars($this->inertiaHeaders()),
                );
            } catch (\Throwable $e) {
                $failures[] = "{$name} [{$method}] istisna firlatti: ".$e::class.': '.$e->getMessage();

                continue;
            }

            $this->releaseLeakedTransactions($name, $failures);

            $status = $response->getStatusCode();

            if ($status === 500) {
                $failures[] = "{$name} [{$method}] 500 dondurdu (Vue bunu router/useForm ile cagiriyor). "
                    .'En yaygin nedeni daraltilmis donus tipi: `: JsonResponse` bildirip Inertia '
                    .'dalinda back()/to_route() dondurmek TypeError firlatir — '
                    .'`: JsonResponse|RedirectResponse` kullanin.';

                continue;
            }

            if ($status === 200 && $response->headers->get('X-Inertia') !== 'true') {
                $failures[] = "{$name} [{$method}] Vue bunu router/useForm ile cagiriyor ama uc "
                    .'ciplak JSON donduruyor — istemcide "All Inertia requests must receive a '
                    .'valid Inertia response" modali acilir ve ekran kilitlenir.';
            }
        }

        $this->assertSame([], $failures, "Inertia protokol ihlali:\n  - ".implode("\n  - ", $failures)."\n");
    }

    public function test_axios_called_write_endpoints_stay_json(): void
    {
        $axiosCalled = self::routeNamesCalledWith('axios');
        $failures = [];

        foreach ($this->panelWriteRoutes() as $name => $method) {
            if (! in_array($name, $axiosCalled, true)) {
                continue;
            }

            $this->migrateScreens([$name]);

            try {
                $response = $this->actingAs($this->owner)->call(
                    $method,
                    route($name),
                    [],
                    [],
                    [],
                    ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest', 'HTTP_ACCEPT' => 'application/json'],
                );
            } catch (\Throwable $e) {
                $failures[] = "{$name} [{$method}] istisna firlatti: ".$e::class.': '.$e->getMessage();

                continue;
            }

            $this->releaseLeakedTransactions($name, $failures);

            if ($response->getStatusCode() === 500) {
                $failures[] = "{$name} [{$method}] axios cagrisinda 500 dondurdu.";
            }
        }

        $this->assertSame([], $failures, "axios veri ucu hatasi:\n  - ".implode("\n  - ", $failures)."\n");
    }

    /**
     * @param  list<string>  $failures
     */
    private function releaseLeakedTransactions(string $routeName, array &$failures): void
    {
        $connection = DB::connection();

        while ($connection->transactionLevel() > 1) {
            $failures[] = "{$routeName} acik DB transaction birakti (commit/rollback yok) — "
                .'uretimde istek boyunca kilit tutar.';

            $connection->rollBack();
        }
    }

    /**
     * @param  string  $callerPattern  regex alternasyonu: 'router|useForm' ya da 'axios'
     * @return list<string>
     */
    private static function routeNamesCalledWith(string $callerPattern): array
    {
        $names = [];

        $roots = array_merge(
            [resource_path('js/panel')],
            glob(base_path('Modules/*/resources/js/panel')) ?: [],
        );

        foreach ($roots as $root) {
            if (! is_dir($root)) {
                continue;
            }

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));

            foreach ($files as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'vue') {
                    continue;
                }

                $src = (string) file_get_contents($file->getPathname());

                $pattern = '/(?:'.$callerPattern.')[\w.]*\.\w+\(\s*route\(\s*[\'"]([^\'"]+)[\'"]/';

                if (preg_match_all($pattern, $src, $m)) {
                    foreach ($m[1] as $routeName) {
                        $names[$routeName] = true;
                    }
                }
            }
        }

        return array_keys($names);
    }

    /**
     * @return array<string, string> route adı => HTTP metodu
     */
    private function panelWriteRoutes(): array
    {
        $panelPath = trim((string) config('settings.admin_panel_path', 'admin'), '/');
        $cases = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            $name = $route->getName();

            if ($name === null || in_array($name, self::SKIP, true)) {
                continue;
            }

            $methods = array_values(array_diff($route->methods(), ['HEAD', 'GET']));

            if ($methods === []) {
                continue;
            }

            $uri = $route->uri();

            if (! str_starts_with($uri, $panelPath.'/') && $uri !== $panelPath) {
                continue;
            }

            $tail = ltrim(substr($uri, strlen($panelPath)), '/');

            if (in_array(explode('/', $tail)[0] ?? '', self::MODULE_SEGMENTS, true)) {
                continue;
            }

            if (preg_match('/\{\w+(?!\?)\}/', $uri)) {
                continue;
            }

            $cases[$name] = $methods[0];
        }

        ksort($cases);

        return $cases;
    }
}
