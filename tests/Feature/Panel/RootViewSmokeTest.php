<?php

namespace Tests\Feature\Panel;

/**
 * Kök panel kabuğunun DUMAN testi.
 *
 * `@routes('panel')` bir Blade direktifiydi ve tightenco/ziggy yüklü olmayan
 * bir ortamda Blade onu DUZ METIN olarak basiyordu: ekranda "@routes('panel')"
 * yazisi, `window.Ziggy` tanimsiz, `route()` firlatiyor, panel bos aciliyor.
 * Hicbir test bunu gormuyordu cunku testler HTML govdesine hic bakmiyordu.
 */
class RootViewSmokeTest extends PanelTestCase
{
    public function test_panel_shell_emits_ziggy_and_no_literal_directive(): void
    {
        $this->migrateScreens(['admin.about']);

        $html = (string) $this->actingAs($this->owner)
            ->get(route('admin.about'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString(
            "@routes(",
            $html,
            'Kok kabukta derlenmemis Blade direktifi DUZ METIN olarak basilmis.'
        );

        $this->assertStringContainsString(
            'window.Ziggy',
            $html,
            'window.Ziggy basilmamis: route() istemcide firlatir ve panel bos acilir.'
        );

        $this->assertStringContainsString(
            'window.__panelLang',
            $html,
            'Ceviri torbasi basilmamis: tum metinler ham anahtar gorunur.'
        );

        $this->assertStringContainsString('data-page', $html, 'Inertia kok elemani yok.');
    }

    public function test_ziggy_payload_contains_panel_routes_only(): void
    {
        $this->migrateScreens(['admin.about']);

        $html = (string) $this->actingAs($this->owner)->get(route('admin.about'))->getContent();

        foreach (['admin.index', 'admin.posts', 'login'] as $name) {
            $this->assertStringContainsString($name, $html, "Ziggy yukunde {$name} yok.");
        }
    }
}
