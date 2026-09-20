<?php

namespace Tests\Feature\Panel;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * `config/panel_inertia_routes.php` — migrasyon defterinin bütünlük testi.
 *
 * Defter hem sunucu render'ını (`PanelResponse`) hem istemci gezinmesini
 * (`inertiaRoutes` paylaşılan prop'u) sürüyor. İçinde çözülemeyen bir ad
 * kalırsa o ekran sessizce Blade'de kalır — hata vermez, sadece taşınmamış gibi
 * davranır. Bu testin yakaladığı şey tam olarak o sessiz düşüş.
 *
 * Joker desenler (`panel.valefix.fault-types.*`) en az bir gerçek route ile
 * eşleşmeli; sıfır eşleşme yazım hatası demektir.
 */
class PanelLedgerTest extends PanelTestCase
{
    public function test_every_ledger_entry_matches_a_real_route(): void
    {
        $patterns = config('panel_inertia_routes', []);

        $this->assertNotEmpty($patterns, 'Migrasyon defteri bos.');

        $names = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            if ($route->getName() !== null) {
                $names[] = $route->getName();
            }
        }

        $disabled = self::disabledModuleKeys();
        $unmatched = [];

        foreach ($patterns as $pattern) {
            /*
             * Devre disi modulun route'lari hic kayit olmaz (Podcast gibi).
             * Deftere yazili olmasi dogru: modul acilir acilmaz ekran Vue
             * olarak sunulmali. Bu yuzden atlanir, hata sayilmaz.
             */
            if (self::belongsToDisabledModule($pattern, $disabled)) {
                continue;
            }

            $matched = false;

            foreach ($names as $name) {
                if (Str::is($pattern, $name)) {
                    $matched = true;
                    break;
                }
            }

            if (! $matched) {
                $unmatched[] = $pattern;
            }
        }

        $this->assertSame(
            [],
            $unmatched,
            'Deftere yazili ama cozulmeyen route desenleri: '.implode(', ', $unmatched)
        );
    }

    /**
     * @return list<string>
     */
    private static function disabledModuleKeys(): array
    {
        $path = base_path('modules_statuses.json');

        if (! is_file($path)) {
            return [];
        }

        $statuses = json_decode((string) file_get_contents($path), true);

        if (! is_array($statuses)) {
            return [];
        }

        $disabled = [];

        foreach ($statuses as $name => $enabled) {
            if (! $enabled) {
                $disabled[] = strtolower((string) $name);
            }
        }

        return $disabled;
    }

    /**
     * @param  list<string>  $disabled
     */
    private static function belongsToDisabledModule(string $pattern, array $disabled): bool
    {
        foreach ($disabled as $key) {
            if (str_starts_with($pattern, 'panel.'.$key.'.')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Defterde aynı ad iki kez varsa biri fazladır: modül blokları birleşirken
     * kopyalanmış demektir.
     */
    public function test_ledger_has_no_duplicates(): void
    {
        $patterns = config('panel_inertia_routes', []);

        $this->assertSame(
            array_values(array_unique($patterns)),
            array_values($patterns),
            'Migrasyon defterinde tekrar eden giris var.'
        );
    }
}
