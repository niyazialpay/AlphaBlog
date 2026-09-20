<?php

namespace Tests\Feature\Panel;

use App\Http\Middleware\HandlePanelInertiaRequests;
use App\Models\Firewall\Firewall;
use App\Models\IPFilter\IPFilter;
use App\Models\Languages;
use App\Models\Settings\GeneralSettings;
use App\Models\Settings\SeoSettings;
use App\Models\User;
use App\Support\Panel\PanelMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Inertia\Inertia;
use ReflectionProperty;
use Tests\TestCase;

/**
 * Panel testleri için ortak bootstrap.
 *
 * Bir panel isteği, uygulamanın normalde GlobalVariableServiceProvider ve
 * Language/NewCommentsCount/SearchedWords middleware'lerinden gelen paylaşılan
 * durumuna ihtiyaç duyar. Bu kurulum daha önce her test dosyasında elle
 * tekrarlanıyordu (`tests/TestCase.php` boş) — burada tek yere alındı.
 *
 * ÖNEMLİ: `HandlePanelInertiaRequests` BİLEREK devre dışı bırakılmaz. Panel
 * testlerinin amacı tam olarak o middleware'in ürettiği yanıtı doğrulamaktır.
 */
abstract class PanelTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected Languages $language;

    protected function setUp(): void
    {
        parent::setUp();

        // Vite manifest'i olmadan kök blade render edilebilsin.
        $this->withoutVite();

        /*
         * Inertia varsayilan olarak asset surumunu Vite manifest'inden turetir.
         * Test istekleri X-Inertia-Version tasimadigi icin her Inertia XHR'i
         * 409 (version conflict) aliyordu. Surum testte devre disi.
         */
        Inertia::version(fn () => null);

        DB::table('languages')->insert([
            'name' => 'Türkçe',
            'code' => 'tr',
            'flag' => 'tr',
            'is_active' => true,
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->language = Languages::first();
        $languages = collect([$this->language]);

        app()->instance('default_language', $this->language);
        app()->instance('languages', $languages);

        $generalSettings = GeneralSettings::firstOrCreate([], []);

        /*
         * Gercek satir olarak yaratiliyor: Language middleware'i seo_settings'i
         * her istekte DB'den yeniden baglar. Sadece app()->instance() ile stub
         * koymak yetmez, eski Blade kabugu app('seo_settings')->site_name okuyor
         * ve null uzerinden 500 aliyordu.
         */
        $seoSettings = SeoSettings::firstOrCreate(
            ['language' => $this->language->code],
            ['site_name' => 'Test', 'title' => 'Test', 'description' => '', 'keywords' => '', 'author' => '', 'robots' => 'index, follow'],
        );

        app()->instance('general_settings', $generalSettings);
        app()->instance('seo_settings', $seoSettings);

        View::share('general_settings', $generalSettings);
        View::share('seo_settings', $seoSettings);
        View::share('languages', $languages);
        View::share('newCommentsCount', 0);
        View::share('searchedWordsCount', 0);

        /*
         * `otp` ve `webauthn` BILEREK acikca veriliyor.
         *
         * User modelinde `WebAuthn(): HasMany` adinda bir iliski var ve PHP metod
         * adlari buyuk/kucuk harf duyarsiz. Factory bu sutunu yazmadiginda model
         * ozniteliklerinde bulunmaz, Eloquent iliskiye duser ve BOS bir Collection
         * doner — bu da PHP'de truthy'dir. Sonucta VerifyOTP her panel testini
         * OTP duvarina carpar. Uretimde kullanici DB'den tum sutunlariyla
         * yuklendigi icin sorun gorunmez.
         */
        $this->owner = User::factory()->create([
            'role' => 'owner',
            'otp' => false,
            'webauthn' => false,
        ]);
    }

    /**
     * Gerçek bir Inertia ziyaretinin başlıkları.
     *
     * Inertia middleware'i asset sürümünü KENDİ version() metodundan set eder
     * (Vite manifest hash'i), bu yüzden Inertia::version() ile testte override
     * etmek işe yaramaz: doğru sürümü göndermeyen her XHR 409 alır.
     *
     * @return array<string, string>
     */
    protected function inertiaHeaders(): array
    {
        return [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => (string) (new HandlePanelInertiaRequests)->version(request()),
            'X-Requested-With' => 'XMLHttpRequest',
        ];
    }

    /**
     * Güvenlik duvarı tekil ayar satırı + zorunlu kara liste kuralı.
     *
     * `FirewallController::index()` `firstOrFail()` çağırıyor ve
     * `firewall.blacklist_rule_id` `ip_filters` tablosuna zorunlu bir yabancı
     * anahtar. setUp'a KONMAZ: `is_active` true bir satır FirewallMiddleware'i
     * devreye sokup ilgisiz panel testlerini engelleyebilir.
     */
    protected function seedFirewall(): Firewall
    {
        $blacklist = IPFilter::create([
            'name' => 'Blacklist',
            'list_type' => 'blacklist',
            'is_active' => true,
            'route_type' => 'select',
            'code' => 403,
        ]);

        IPFilter::create([
            'name' => 'Whitelist',
            'list_type' => 'whitelist',
            'is_active' => true,
            'route_type' => 'select',
            'code' => 403,
        ]);

        return Firewall::create([
            'is_active' => false,
            'blacklist_rule_id' => $blacklist->id,
        ]);
    }

    /**
     * Belirli ekranları Vue olarak sunulacak şekilde işaretler (migrasyon defteri).
     *
     * @param  list<string>  $routeNames
     */
    protected function migrateScreens(array $routeNames): void
    {
        config()->set('panel_inertia_routes', $routeNames);
        config()->set('settings.panel_ui', 'vue');
        config()->set('settings.panel_ui_screens', '');

        /*
         * `PanelMenu::$ledger` defteri SUREC OMRU boyunca memoize ediyor (uretimde
         * istek basina bir kez okunsun diye). Statik olduğu icin testler arasinda
         * da yasiyor: config'i degistirmek tek basina yetmiyordu ve defteri ilk
         * dolduran test butun sureci kilitliyordu. Sonuc siralamaya bagliydi —
         * tam suit calisirken gercek config once yuklendigi icin her sey
         * gecerken, `--filter` ile calisan bir alt kume ilk daraltmaya takilip
         * Inertia yerine Blade yanitlari aliyordu.
         */
        $ledger = new ReflectionProperty(PanelMenu::class, 'ledger');
        $ledger->setValue(null, null);
    }
}
