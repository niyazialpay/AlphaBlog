<?php

namespace Tests\Feature\Panel;

use Inertia\Testing\AssertableInertia;

/**
 * `PostController::wantsDataTable()` kapısının testi.
 *
 * Migrasyonun en sinsi hatası buradaydı: `@inertiajs/core` HER XHR ziyaretinde
 * `X-Requested-With: XMLHttpRequest` gönderiyor ve `Request::ajax()` tam olarak
 * bunu kontrol ediyor. Kapı olmadan `admin.posts` Inertia sayfası olduğunda
 * sidebar'dan yapılan HER `<Link>` gezinmesi DataTables JSON'u alır — ilk tam
 * sayfa yüklemesi çalıştığı için naif bir duman testi bunu YAKALAMAZ.
 *
 * Kapı: `$request->ajax() && ! $request->inertia() && $request->has('draw')`
 *
 * Yan etki: eski uç her ziyarette `session('post_datatable_length')`'i eziyordu
 * ve bu anahtarı üç ilgisiz tablo daha okuyor.
 */
class PostDataTableCompatTest extends PanelTestCase
{
    /**
     * Eski jQuery DataTables istemcisi DAİMA `draw` gönderir → JSON dalı.
     */
    public function test_legacy_datatable_request_still_receives_json(): void
    {
        $this->migrateScreens(['admin.posts']);

        $response = $this->actingAs($this->owner)->get(
            route('admin.posts', ['type' => 'blogs']).'?draw=1&start=0&length=10',
            ['X-Requested-With' => 'XMLHttpRequest'],
        );

        $response->assertOk();
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }

    /**
     * Inertia gezinmesi `X-Requested-With` GÖNDERİR ama `draw` göndermez →
     * Inertia yanıtı dönmeli, DataTables JSON'u değil.
     */
    public function test_inertia_visit_receives_inertia_response(): void
    {
        $this->migrateScreens(['admin.posts']);

        $response = $this->actingAs($this->owner)->get(
            route('admin.posts', ['type' => 'blogs']),
            $this->inertiaHeaders(),
        );

        $response->assertOk();
        $response->assertHeader('X-Inertia', 'true');
        $response->assertJsonPath('component', 'Posts/Index');
    }

    /**
     * Tam sayfa yüklemesi de Inertia bileşenini render etmeli.
     */
    public function test_full_page_load_renders_inertia_component(): void
    {
        $this->migrateScreens(['admin.posts']);

        $this->actingAs($this->owner)
            ->get(route('admin.posts', ['type' => 'blogs']))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Posts/Index'));
    }

    /**
     * Inertia ziyareti `post_datatable_length` oturum anahtarını EZMEMELİ.
     * Bu anahtarı üç ilgisiz tablo daha okuyor; her gezinmede null'a düşmesi
     * onların sayfa boyutunu sessizce sıfırlardı.
     */
    public function test_inertia_visit_does_not_clobber_datatable_length_session(): void
    {
        $this->migrateScreens(['admin.posts']);

        $this->withSession(['post_datatable_length' => 75])
            ->actingAs($this->owner)
            ->get(route('admin.posts', ['type' => 'blogs']), $this->inertiaHeaders())
            ->assertOk();

        $this->assertSame(75, session('post_datatable_length'));
    }
}
