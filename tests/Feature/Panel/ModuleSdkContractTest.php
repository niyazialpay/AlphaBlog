<?php

namespace Tests\Feature\Panel;

use Illuminate\Support\Facades\Route;
use Nwidart\Modules\Facades\Module;

/**
 * Modül panel SDK sözleşmesinin testi.
 *
 * Modül kodu çekirdek repoya girmez (`/Modules` gitignore'lu), bu yüzden bu test
 * "modül X doğru taşınmış mı" demez — SÖZLEŞMEYİ doğrular: etkin modüllerden
 * hangisi bir menü yayınlıyorsa şeması geçerli olmalı ve bildirdiği her route
 * adı gerçekten çözülmelidir.
 *
 * Bunun yakaladığı hata sınıfı pahalıdır: menüde var olmayan bir route adı
 * `route()` çağrısında RouteNotFoundException fırlatır ve TÜM panel sidebar'ını
 * çökertir — modülün kendi ekranlarını değil, panelin tamamını.
 */
class ModuleSdkContractTest extends PanelTestCase
{
    public function test_enabled_module_menus_declare_resolvable_routes(): void
    {
        if (! class_exists(Module::class)) {
            $this->markTestSkipped('nwidart/laravel-modules kurulu degil.');
        }

        $checked = 0;

        foreach (Module::allEnabled() as $module) {
            $path = $module->getPath().'/config/panel_menu.php';

            if (! is_file($path)) {
                // Katman 1 yazilmamis modul: route tablosundan otomatik kesfe duser.
                continue;
            }

            $checked++;
            $section = require $path;

            $this->assertIsArray($section, $module->getName().': panel_menu.php dizi dondurmeli.');
            $this->assertArrayHasKey('items', $section, $module->getName().': items eksik.');
            $this->assertIsArray($section['items'], $module->getName().': items dizi olmali.');

            foreach ($section['items'] as $index => $item) {
                $label = $module->getName().' items['.$index.']';

                $this->assertIsArray($item, $label.': dizi olmali.');
                $this->assertArrayHasKey('route', $item, $label.': route adi eksik.');

                $this->assertNotNull(
                    Route::getRoutes()->getByName($item['route']),
                    $label.': "'.$item['route'].'" route adi cozulmuyor.'
                );
            }
        }

        // Hicbir modul menu yayinlamamis olabilir; test yine de anlamlidir.
        $this->assertGreaterThanOrEqual(0, $checked);
    }

    /**
     * Modül Vue sayfaları kendi dizinlerinde yaşar ve tek build'e glob'lanır.
     * Bir modül Pages dizini açtıysa bileşen dosyalarının okunabilir olduğunu
     * doğrula — boş/erişilemez dizin, build'de sessizce kaybolan ekran demektir.
     */
    public function test_module_panel_page_directories_are_readable(): void
    {
        if (! class_exists(Module::class)) {
            $this->markTestSkipped('nwidart/laravel-modules kurulu degil.');
        }

        foreach (Module::allEnabled() as $module) {
            $pages = $module->getPath().'/resources/js/panel/Pages';

            if (! is_dir($pages)) {
                continue;
            }

            $files = glob($pages.'/**/*.vue') ?: [];
            $files = array_merge($files, glob($pages.'/*.vue') ?: []);

            $this->assertNotEmpty(
                $files,
                $module->getName().': Pages dizini var ama icinde .vue yok.'
            );
        }

        $this->assertTrue(true);
    }
}
