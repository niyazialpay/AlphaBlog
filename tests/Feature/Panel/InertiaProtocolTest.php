<?php

namespace Tests\Feature\Panel;

use App\Models\Post\Posts;

/**
 * Inertia PROTOKOL testleri.
 *
 * Bu sınıfın yakaladığı hata sınıfı elle test edilirken görünmez: tam sayfa
 * yüklemesi (URL yazmak) çalışırken, sidebar'dan yapılan Inertia ziyareti
 * bozulur. Kaynak, `Request::ajax()`'in Inertia XHR'ları için de true dönmesi
 * ve middleware'lerin doğrudan Blade döndürmesidir.
 */
class InertiaProtocolTest extends PanelTestCase
{
    /**
     * `PostController::index` `$request->ajax()` ile dallanıyordu; Inertia her
     * ziyarette `X-Requested-With: XMLHttpRequest` gönderdiği için sidebar'dan
     * Bloglar'a her geçiş sayfa yerine DataTables JSON'u alıyordu.
     *
     * Eski jQuery DataTables her zaman `draw` gönderir — ayırt edici koşul budur.
     */
    public function test_inertia_visit_to_posts_does_not_return_datatables_json(): void
    {
        $this->migrateScreens(['admin.posts']);

        $response = $this->actingAs($this->owner)
            ->withHeaders($this->inertiaHeaders())
            ->get(route('admin.posts', ['type' => 'blogs']));

        $content = $response->getContent();

        $this->assertStringNotContainsString(
            '"recordsTotal"',
            (string) $content,
            'Inertia ziyareti DataTables beslemesine düştü.',
        );
    }

    /**
     * Eski DataTables sözleşmesi bozulmadı: `draw` gönderen istek hâlâ
     * yajra yanıtını alır. Bu, geçiş dönemi boyunca Blade ekranlarının
     * çalışmaya devam etmesinin garantisidir.
     */
    public function test_legacy_datatables_request_still_returns_feed(): void
    {
        Posts::factory()?->count(0);

        $response = $this->actingAs($this->owner)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('admin.posts', ['type' => 'blogs', 'draw' => 1, 'start' => 0, 'length' => 10]));

        $response->assertOk();
        $this->assertStringContainsString('recordsTotal', (string) $response->getContent());
    }

    /**
     * VerifyOTP middleware'i doğrudan Blade döndürüyordu. Inertia XHR'ına 200 +
     * HTML gövde gelirse client sonsuz yeniden yükleme / hata modalı üretir.
     */
    public function test_otp_wall_returns_inertia_response_for_inertia_visits(): void
    {
        $this->migrateScreens(['admin.about']);

        $this->owner->forceFill(['otp' => true])->save();

        $this->actingAs($this->owner)
            ->withHeaders($this->inertiaHeaders())
            ->get(route('admin.about'))
            ->assertOk()
            // assertInertia() yalnizca TAM SAYFA (HTML) Inertia yanitlarini okur;
            // burada asil sozlesme XHR govdesidir.
            ->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Auth/Otp');
    }

    /**
     * Aynı duvar, tam sayfa yüklemesinde (ve tüm modül ekranlarında) hâlâ eski
     * Blade görünümünü vermeli — Auth ekranları taşınana kadar.
     */
    public function test_otp_wall_keeps_blade_for_full_page_loads(): void
    {
        $this->migrateScreens(['admin.about']);

        $this->owner->forceFill(['otp' => true])->save();

        $this->actingAs($this->owner)
            ->get(route('admin.about'))
            ->assertOk()
            ->assertViewIs('panel.auth.otp');
    }
}
