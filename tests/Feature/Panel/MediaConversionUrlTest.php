<?php

namespace Tests\Feature\Panel;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MediaConversionUrlTest extends TestCase
{
    #[Test]
    public function the_helper_handles_a_missing_media_row(): void
    {
        $this->assertNull(mediaConversionUrl(null, 'resized'));
        $this->assertNull(mediaConversionUrl(null, 'resized', fallbackToOriginal: false));
    }

    #[Test]
    public function no_panel_controller_builds_a_conversion_url_without_checking_it_exists(): void
    {
        $offenders = [];

        foreach ($this->panelControllers() as $file) {
            $source = (string) file_get_contents($file);

            foreach (preg_split('/\R/', $source) ?: [] as $number => $line) {
                if (! $this->buildsAnUncheckedConversionUrl($line)) {
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

        $this->assertSame(
            [],
            $offenders,
            "Donusum URL'i korumasiz uretiliyor — dosya yoksa 404 doner.\n"
            ."mediaConversionUrl(\$media, 'conversion') kullanin.\n  "
            .implode("\n  ", $offenders)
        );
    }

    private function buildsAnUncheckedConversionUrl(string $line): bool
    {
        $trimmed = ltrim($line);

        if ($trimmed === '' || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '//') || str_starts_with($trimmed, '/*')) {
            return false;
        }

        if (str_contains($line, 'mediaConversionUrl(')) {
            return false;
        }

        return preg_match("/getFullUrl\(\s*'[^']+'\s*\)/", $line) === 1
            || preg_match("/getFirstMediaUrl\(\s*'[^']+'\s*,\s*'[^']+'\s*\)/", $line) === 1;
    }

    /**
     * @return list<string>
     */
    private function panelControllers(): array
    {
        $files = [];

        foreach (['app/Http/Controllers/Admin', 'app/Support/Panel'] as $directory) {
            $path = base_path($directory);

            if (! is_dir($path)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }
}
