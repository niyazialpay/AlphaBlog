<?php

namespace Tests\Feature\Panel;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;
use PHPUnit\Framework\Attributes\Test;
use ReflectionObject;
use Tests\TestCase;

/**
 * `web` middleware grubunun SIRASINI kilitler.
 *
 * NEDEN VAR: `bootstrap/app.php` bir donem `StartSession`'i gruba PREPEND
 * ediyordu. Laravel prepend'i `array_unique(array_merge($prepends, $group))`
 * ile birlestirdigi icin ayni sinif iki listede bulundugunda ILK gecis kaliyor
 * ve gruptaki asil sirasi DUSUYORDU. Sonuc: oturum `EncryptCookies`'ten once
 * basliyor, oturum ve `XSRF-TOKEN` cerezleri sifrelenmeden yazilip okunuyor,
 * sunucu ise `X-XSRF-TOKEN` basligini decrypt etmeye calisip basarisiz oluyor
 * ve cerez tabanli CSRF yolu tamamen oluyordu — panelde her POST 419.
 *
 * Hicbir test bunu yakalamamisti: CSRF middleware'i `runningUnitTests()` ile
 * testlerde tamamen atlaniyor, dolayisiyla yesil suit bu hatayi goremez.
 * Bu yuzden dogrulama davranisa degil, SIRAYA bakiyor.
 */
class WebMiddlewareOrderTest extends TestCase
{
    #[Test]
    public function cookies_are_decrypted_before_the_session_starts_and_csrf_runs(): void
    {
        $kernel = $this->app->make(Kernel::class);

        $property = (new ReflectionObject($kernel))->getProperty('middlewareGroups');
        $group = $property->getValue($kernel)['web'];

        $encrypt = array_search(EncryptCookies::class, $group, true);
        $session = array_search(StartSession::class, $group, true);
        $csrf = array_search(VerifyCsrfToken::class, $group, true);

        $this->assertIsInt($encrypt, 'EncryptCookies web grubunda olmali.');
        $this->assertIsInt($session, 'StartSession web grubunda olmali.');
        $this->assertIsInt($csrf, 'VerifyCsrfToken web grubunda olmali.');

        $this->assertLessThan($session, $encrypt, 'Cerezler oturumdan ONCE cozulmeli.');
        $this->assertLessThan($csrf, $session, 'Oturum CSRF kontrolunden ONCE baslamali.');

        $this->assertSame(
            1,
            count(array_keys($group, StartSession::class, true)),
            'StartSession gruba birden fazla kez girmemeli.'
        );
    }
}
