<?php

namespace Tests\Feature\Panel;

use FilesystemIterator;
use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

class PanelResponseShapeTest extends TestCase
{
    #[Test]
    public function no_panel_endpoint_picks_its_response_shape_from_the_inertia_header(): void
    {
        $offenders = [];

        foreach ($this->panelPhpFiles() as $file) {
            $lines = preg_split('/\R/', (string) file_get_contents($file)) ?: [];

            foreach ($lines as $number => $line) {
                if (! $this->isResponseShapeTernary($line, $lines, $number)) {
                    continue;
                }

                $offenders[] = sprintf(
                    '%s:%d  %s',
                    ltrim(str_replace([base_path(), DIRECTORY_SEPARATOR], ['', '/'], $file), '/'),
                    $number + 1,
                    trim($line)
                );
            }
        }

        sort($offenders);

        $this->assertSame(
            [],
            $offenders,
            "Yanit sekli X-Inertia basligina gore seciliyor.\n"
            ."Form eylemi HER ZAMAN yonlendirmeli, veri ucu HER ZAMAN JSON donmeli:\n  "
            .implode("\n  ", $offenders)
        );
    }

    /**
     * @param  list<string>  $lines
     */
    private function isResponseShapeTernary(string $line, array $lines, int $number): bool
    {
        $trimmed = ltrim($line);

        if ($trimmed === '' || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '//') || str_starts_with($trimmed, '/*')) {
            return false;
        }

        if (! str_contains($line, 'inertia()')) {
            return false;
        }

        if (preg_match('/inertia\(\)\s*\?/', $line) === 1) {
            return true;
        }

        if (preg_match('/inertia\(\)\s*$/', rtrim($line)) !== 1) {
            return false;
        }

        for ($i = $number + 1; $i < count($lines); $i++) {
            $next = ltrim($lines[$i]);

            if ($next === '') {
                continue;
            }

            return str_starts_with($next, '?');
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function panelPhpFiles(): array
    {
        $roots = [
            base_path('app/Http/Controllers/Admin'),
            base_path('app/Actions'),
        ];

        foreach (glob(base_path('Modules/*/app/Http/Controllers'), GLOB_ONLYDIR) ?: [] as $dir) {
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
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }
}
