<?php

namespace Tests\Feature\Panel;

use FilesystemIterator;
use Illuminate\Support\Facades\Lang;
use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class PanelTranslationKeysTest extends PanelTestCase
{
    #[Test]
    public function every_literal_translation_key_used_by_the_panel_resolves(): void
    {
        $keys = [];

        foreach ($this->panelVueFiles() as $file) {
            foreach ($this->keysIn((string) file_get_contents($file)) as $key) {
                $keys[$key][] = $file;
            }
        }

        $this->assertNotEmpty($keys, 'Hicbir ceviri anahtari bulunamadi — tarayici bozulmus olabilir.');

        $missing = [];
        $hints = array_keys(Lang::getLoader()->namespaces());

        foreach ($keys as $key => $files) {
            if (str_contains($key, '::') && ! in_array(strstr($key, '::', true), $hints, true)) {
                continue;
            }

            foreach (['tr', 'en'] as $locale) {
                if (Lang::has($key, $locale) && is_string(Lang::get($key, [], $locale))) {
                    continue;
                }

                $reason = Lang::has($key, $locale) ? 'dizi donuyor' : 'anahtar yok';
                $missing[] = "[{$locale}] {$key} ({$reason}) — ".$this->relative($files[0]);
            }
        }

        sort($missing);

        $this->assertSame(
            [],
            $missing,
            "Panelde ekrana HAM ANAHTAR olarak basilacak ceviriler:\n  ".implode("\n  ", $missing)
        );
    }

    /**
     * @return list<string>
     */
    private function panelVueFiles(): array
    {
        $roots = [base_path('resources/js/panel')];

        foreach (glob(base_path('Modules/*/resources/js/panel'), GLOB_ONLYDIR) ?: [] as $dir) {
            $roots[] = $dir;
        }

        $files = [];

        foreach ($roots as $root) {
            if (! is_dir($root)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'vue') {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    /**
     * @return list<string>
     */
    private function keysIn(string $source): array
    {
        $quote = chr(39);
        $pattern = '/(?:__|[$]t|transChoice)[(][[:space:]]*'
            .$quote.'([^'.$quote.'$]+[.][^'.$quote.'$]+)'.$quote.'/';

        preg_match_all($pattern, $source, $matches);

        return array_values(array_unique($matches[1]));
    }

    private function relative(string $path): string
    {
        return ltrim(str_replace([base_path(), DIRECTORY_SEPARATOR], ['', '/'], $path), '/');
    }
}
