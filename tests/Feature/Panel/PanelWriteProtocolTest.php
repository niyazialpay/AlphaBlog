<?php

namespace Tests\Feature\Panel;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * Panel yazma uçlarının Inertia protokol testi.
 *
 * NEDEN VAR: migrasyon sonrası 231 test yeşildi ama panelde HER form gönderimi
 * 500 veriyordu. Testlerin tamamı GET render yollarını kapsıyordu; tek bir POST
 * ucu bile Inertia başlıklarıyla çağrılmamıştı. Çelişmeli denetim 13 blocker
 * buldu, 8'i tek bir sınıftan:
 *
 *     public function save(...): JsonResponse        // <- daraltılmış dönüş tipi
 *     {
 *         return $request->inertia()
 *             ? back()->with('success', ...)          // <- RedirectResponse
 *             : response()->json([...]);
 *     }
 *
 * PHP sınıf dönüş tiplerinde coercion yapmaz: her Inertia POST'u yakalanmamış
 * TypeError → 500.
 *
 * BEKLENTİ VUE'DAN TÜRETİLİR, VARSAYILMAZ. Panelin R1 kuralı şudur: form
 * eylemleri yönlendirir, VERİ eylemleri JSON kalır. Bu yüzden bir ucun Inertia
 * konuşup konuşmayacağını Vue tarafındaki ÇAĞRI BİÇİMİ belirler:
 *
 *   router.post(route('x'))  /  useForm().post(route('x'))  → Inertia dönmeli
 *   axios.post(route('x'))                                  → JSON kalmalı
 *
 * Böylece `general.search` ya da `admin.system-logs.data` gibi meşru JSON
 * beslemeleri yanlışlıkla ihlal sayılmaz.
 */
class PanelWriteProtocolTest extends PanelTestCase
{
    /**
     * Protokolden muaf DEĞİL; bu testte güvenilir çalıştırılamayanlar
     * (dış servis, oturum imhası, kimlik değiştirme, tarayıcı kriptosu).
     *
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

        // Fortify kendi yanıt sözleşmesini döndürür.
        'two-factor.enable',
        'two-factor.confirm',
        'two-factor.disable',
    ];

    /**
     * Modül segmentleri.
     *
     * Modül yazma uçları DIŞLANIR — protokolden muaf oldukları için değil, test
     * ortamında URL'leri üretilemediği için. Bazı modüller ön yüz route'larını
     * `Route::group(['prefix' => '/{language}'])->whereIn('language',
     * Languages::all()->pluck('code'))` ile kaydediyor; `Languages::all()` ROUTE
     * KAYIT anında çalışıyor ve `RefreshDatabase` dil satırını bundan SONRA
     * eklediği için dizi boş kalıyor, requirement boş regex'e düşüyor ve
     * `route()` "Routing requirement for 'language' cannot be empty" fırlatıyor.
     * Aynı kök neden `php artisan route:list`i de bu projede asıyor.
     *
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

            // Her ucu kendi defterine yazili gibi calistir.
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

    /**
     * axios ile çağrılan uçlar JSON KALMALI (R1).
     *
     * Ters yön de bir hatadır: bir veri ucu `back()` döndürmeye başlarsa axios
     * çağıranı `response.data.status` okur, `undefined` alır ve sessizce yanlış
     * dala düşer — hata bile vermez.
     */
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
     * Açık kalmış DB transaction'ını yakalar ve geri alır.
     *
     * Bir controller `DB::beginTransaction()` açıp commit/rollback yapmadan
     * dönerse (ör. transaction içinde fırlayan ve yakalanmayan bir hata),
     * bağlantı transaction içinde kalır. Üretimde bu, istek bitene kadar kilit
     * tutar; testte ise SONRAKİ testin setUp'ını "cannot start a transaction
     * within a transaction" ile düşürür ve hata YANLIŞ teste atfedilir.
     *
     * Burada hem raporlanır hem temizlenir ki kalan uçlar ölçülebilsin.
     *
     * @param  list<string>  $failures
     */
    private function releaseLeakedTransactions(string $routeName, array &$failures): void
    {
        $connection = DB::connection();

        // RefreshDatabase kendi sarmalayici transaction'ini tutar (seviye 1).
        while ($connection->transactionLevel() > 1) {
            $failures[] = "{$routeName} acik DB transaction birakti (commit/rollback yok) — "
                .'uretimde istek boyunca kilit tutar.';

            $connection->rollBack();
        }
    }

    /**
     * Vue kaynağında belirtilen çağrı biçimiyle kullanılan route adları.
     *
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

                // router.post(route('x'  |  form.post(route('x'  |  axios.post(route('x'
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
     * Zorunlu segment taşımayan çekirdek panel yazma route'ları.
     *
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
