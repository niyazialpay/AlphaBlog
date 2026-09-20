<?php

namespace Tests\Feature\Panel;

/**
 * Geçiş dönemi kanaryası.
 *
 * `resources/views/panel/base.blade.php`, `partials/menu.blade.php` ve
 * `resources/js/panel.js` henüz taşınmamış çekirdek ekranları VE 7 modülün
 * 97 panel blade'ini besliyor. Bu üçü, son modül ekranı Vue'ya taşınana kadar
 * silinemez ya da bozulamaz.
 *
 * Bu test kırmızıya dönerse 7 sitenin yönetim paneli birden bozulmuş demektir.
 */
class LegacyShellTest extends PanelTestCase
{
    public function test_unmigrated_screen_still_renders_through_adminlte_shell(): void
    {
        // Migrasyon defteri boş: hiçbir ekran Vue değil.
        $this->migrateScreens([]);

        $response = $this->actingAs($this->owner)->get(route('admin.users'));

        $response->assertOk();
        $response->assertViewIs('panel.user.index');

        $content = (string) $response->getContent();

        // Eski kabuğun taşıyıcı parçaları yerinde mi?
        $this->assertStringContainsString('adminlte', $content, 'AdminLTE varlıkları kayboldu.');
        $this->assertStringContainsString('jquery', $content, 'jQuery kayboldu.');
        $this->assertStringContainsString('content-wrapper', $content, 'AdminLTE iskeleti kayboldu.');
    }

    public function test_legacy_panel_shell_files_exist(): void
    {
        foreach ([
            'resources/views/panel/base.blade.php',
            'resources/views/panel/partials/menu.blade.php',
            'resources/views/panel/partials/header-navbar.blade.php',
            'resources/js/panel.js',
        ] as $path) {
            $this->assertFileExists(base_path($path), "Eski panel kabuğunun parçası silinmiş: {$path}");
        }
    }

    /**
     * Modül panel route'ları çekirdek panel grubunda DEĞİL, her modülün kendi
     * RouteServiceProvider'ında kayıtlı. Yine de hepsi admin panel path'i ile
     * prefixli olmalı — panel middleware'inin yol tabanlı tespiti buna dayanıyor.
     */
    public function test_module_panel_routes_live_under_the_panel_prefix(): void
    {
        $panelPath = config('settings.admin_panel_path');
        $moduleRoutes = collect(app('router')->getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with((string) $route->getName(), 'panel.'));

        if ($moduleRoutes->isEmpty()) {
            $this->markTestSkipped('Bu kurulumda etkin modül paneli yok.');
        }

        foreach ($moduleRoutes as $route) {
            $this->assertStringStartsWith(
                $panelPath.'/',
                $route->uri(),
                "Modül route'u panel prefix'i dışında: {$route->getName()} ({$route->uri()})",
            );
        }
    }
}
