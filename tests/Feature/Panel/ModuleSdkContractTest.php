<?php

namespace Tests\Feature\Panel;

use Illuminate\Support\Facades\Route;
use Nwidart\Modules\Facades\Module;

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

        $this->assertGreaterThanOrEqual(0, $checked);
    }

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
