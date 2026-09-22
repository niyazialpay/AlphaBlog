<?php

namespace Tests\Feature\Panel;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;
use PHPUnit\Framework\Attributes\Test;
use Throwable;

class PanelMenuLabelsTest extends PanelTestCase
{
    #[Test]
    public function every_core_menu_label_resolves_in_both_locales(): void
    {
        $this->assertLabelsResolve($this->labelsOf(config('panel_menu', [])), 'config/panel_menu.php');
    }

    #[Test]
    public function every_enabled_module_menu_label_resolves_in_both_locales(): void
    {
        if (! class_exists(Module::class)) {
            $this->markTestSkipped('nwidart/laravel-modules kurulu degil.');
        }

        try {
            $modules = Module::allEnabled();
        } catch (Throwable) {
            $this->markTestSkipped('Modul listesi okunamadi.');
        }

        $checked = 0;

        foreach ($modules as $module) {
            $path = $module->getPath().'/config/panel_menu.php';

            if (! is_file($path)) {
                continue;
            }

            $section = require $path;

            if (! is_array($section)) {
                continue;
            }

            $this->assertLabelsResolve($this->labelsOf([$section]), $path);
            $checked++;
        }

        $this->assertGreaterThan(0, $checked, 'Hicbir modul menu yayini bulunamadi.');
    }

    /**
     * @param  array<int, mixed>  $sections
     * @return list<string>
     */
    private function labelsOf(array $sections): array
    {
        $labels = [];

        foreach ($sections as $section) {
            if (! is_array($section)) {
                continue;
            }

            if (isset($section['label']) && is_string($section['label'])) {
                $labels[] = $section['label'];
            }

            foreach ($section['items'] ?? [] as $item) {
                if (is_array($item) && isset($item['label']) && is_string($item['label'])) {
                    $labels[] = $item['label'];
                }
            }
        }

        return array_values(array_unique($labels));
    }

    /**
     * @param  list<string>  $labels
     */
    private function assertLabelsResolve(array $labels, string $source): void
    {
        $missing = [];

        foreach ($labels as $label) {
            if (! $this->looksLikeTranslationKey($label)) {
                continue;
            }

            foreach (['tr', 'en'] as $locale) {
                if (! Lang::has($label, $locale) || ! is_string(Lang::get($label, [], $locale))) {
                    $missing[] = "[{$locale}] {$label}";
                }
            }
        }

        $this->assertSame(
            [],
            $missing,
            $source." icinde cozulemeyen menu etiketleri (ekranda ham anahtar gorunur):\n  ".implode("\n  ", $missing)
        );
    }

    private function looksLikeTranslationKey(string $label): bool
    {
        return str_contains($label, '.')
            && ! str_contains($label, ' ')
            && Str::lower($label) === $label;
    }
}
