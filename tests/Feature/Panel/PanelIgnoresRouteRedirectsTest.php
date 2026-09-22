<?php

namespace Tests\Feature\Panel;

use App\Actions\RouteRedirectAction;
use App\Models\RouteRedirects;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;

/**
 * Site yonlendirme tablosunun YONETIM PANELINI kacirmadigini kilitler.
 *
 * `RouteRedirect` GLOBAL bir middleware: her istege bakiyor ve `route_redirects`
 * tablosunda `old_url` ile birebir eslesme ararsa `redirect()` donuyor. Panel
 * URL'leri de bu taramaya giriyordu.
 *
 * Sonuc: panelde bir ekran acilmiyordu. Inertia ziyareti sayfa yerine bir
 * yonlendirme govdesi aliyor, JSON/HTML ayrimini yapamayip hata modalini
 * aciyor ve kullanici bos ekran goruyordu — uretimde notlar ekraninda tam
 * olarak bu yasandi ("200 — Redirecting to .../panel/notes").
 *
 * Yonlendirme tablosu SITE icindir; panel ona tabi degildir.
 */
class PanelIgnoresRouteRedirectsTest extends PanelTestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_redirect_rule_matching_a_panel_url_is_ignored(): void
    {
        $path = trim(config('settings.admin_panel_path'), '/').'/notes';

        RouteRedirects::create([
            'old_url' => $path,
            'new_url' => '/tuzak',
            'redirect_code' => 301,
        ]);

        $request = Request::create('/'.$path, 'GET');

        $this->assertNull(
            RouteRedirectAction::RouteRedirect($request),
            'Panel URL\'i yonlendirme tablosundan muaf olmali.'
        );
    }

    #[Test]
    public function a_redirect_rule_still_applies_to_the_public_site(): void
    {
        RouteRedirects::create([
            'old_url' => 'eski/yazi',
            'new_url' => '/yeni/yazi',
            'redirect_code' => 301,
        ]);

        $request = Request::create('/eski/yazi', 'GET');

        $route = RouteRedirectAction::RouteRedirect($request);

        $this->assertNotNull($route, 'Site yonlendirmeleri calismaya devam etmeli.');
        $this->assertSame('/yeni/yazi', $route->new_url);
    }
}
