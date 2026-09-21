<?php

namespace Tests\Feature\Panel;

use FilesystemIterator;
use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

/**
 * Panel yazma uclarinin TEK bir yanit sekli oldugunu kilitler.
 *
 * YASAKLANAN KALIP:
 *
 *     return $request->inertia()
 *         ? back()->with('success', ...)
 *         : response()->json([...]);
 *
 * NEDEN: bu ucluk yaniti `X-Inertia` istek basliginin agda sag kalmasina
 * baglar. Uretimde kalmadi. Kullanici "gorsel sil" dedi, uc Inertia ziyaretine
 * `200 {"status":true}` dondu; Inertia JSON'i sayfa sayamadigi icin tam ekran
 * hata modalini acti ve ekranda bembeyaz bir kutu kaldi. Ayni sinif baska
 * ekranlarda "Redirecting" yazan bos bir modal uretti.
 *
 * DOGRUSU (CLAUDE.md, R1): form eylemi HER ZAMAN yonlendirir, veri ucu HER
 * ZAMAN JSON doner. Hangisi oldugu ucun isine gore SABITTIR; istek basligina
 * gore degismez.
 *
 * MUAF: `$request->inertia()` bir YONLENDIRME KORUMASI olarak kullanilabilir —
 * ornegin `PostController::wantsDataTable()` eski DataTables beslemesini Inertia
 * ziyaretinden ayirmak icin `$request->ajax() && ! $request->inertia() &&
 * $request->has('draw')` diyor. Bu yanit sekli secmiyor, hangi ISIN
 * yapilacagini seciyor. Test yalnizca UCLUK bicimini yasaklar.
 */
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
     * `inertia()` ucluk bicimini yakalar; tek satirda da, sonraki satira sarkan
     * `? ... : ...` biciminde de.
     *
     * @param  list<string>  $lines
     */
    private function isResponseShapeTernary(string $line, array $lines, int $number): bool
    {
        $trimmed = ltrim($line);

        // Yorumlar kod degildir.
        if ($trimmed === '' || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '//') || str_starts_with($trimmed, '/*')) {
            return false;
        }

        if (! str_contains($line, 'inertia()')) {
            return false;
        }

        // Ayni satirda ucluk: `$request->inertia() ? ... : ...`
        if (preg_match('/inertia\(\)\s*\?/', $line) === 1) {
            return true;
        }

        // Sonraki satira sarkan ucluk: satir `inertia()` ile bitiyor ve
        // sonraki dolu satir `?` ile basliyor.
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
