<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Eski `addslashes()` kacislarini veritabanindan BIR KEZ temizler.
 *
 * GECMIS: `GetPost()` ve `content()` yardimcilari metni `addslashes()` ile
 * kacisli yaziyor, okuma tarafi `stripslashes()` ile geri cozuyordu. Bu kalip
 * kaldirildi (bkz. app/Helpers/functions.php): kacis her duzenlemede
 * katlaniyordu ve okuma tarafindaki `stripslashes()` icerikte GERCEKTEN bulunan
 * ters bolulari (kod ornekleri, dosya yollari, regex'ler) yiyordu.
 *
 * NEDEN PHP'NIN `stripslashes()` FONKSIYONU KULLANILMIYOR: o, ters boluyu
 * HANGI karakterden once olursa olsun siler. Bir yazilim blogunda yikici olur:
 * "C:\Users" -> "C:Users", "\d+" -> "d+". Bu komut yalnizca `addslashes()`'in
 * URETTIGI uc diziyi cozer — tirnak onundeki ters bolu ve cift ters bolu —
 * baska hicbir ters boluya dokunmaz.
 *
 * Tarama TEK YONLU ve soldan sagadir: cozulen bir kacisin ciktisi yeniden
 * taranmaz. Bu sayede "kacisli ters bolu + gercek tirnak" dizisi dogru sonuca
 * iner, ardisik `str_replace` cagrilarinin yaptigi gibi bozulmaz.
 *
 * DIKKAT — GERI ALINAMAZ. Once yedek alin:
 *     mysqldump -u KULLANICI -p VERITABANI > yedek.sql
 *
 * Varsayilan KURU CALISMA'dir; yazmak icin `--force` gerekir.
 */
class UnescapeLegacySlashes extends Command
{
    protected $signature = 'db:unescape-slashes
        {--force : Degisiklikleri gercekten yaz (varsayilan: yalnizca rapor)}
        {--table=* : Yalnizca bu tablolari isle}
        {--chunk=500 : Tek seferde islenecek satir sayisi}
        {--samples=3 : Tablo basina gosterilecek ornek sayisi}';

    protected $description = 'Eski addslashes kacislarini veritabanindan temizler (tirnak onu ve cift ters bolu)';

    /**
     * Islenecek tablo => kolon haritasi.
     *
     * Yalnizca `GetPost()` / `content()` uzerinden yazilan ya da temalarda
     * `stripslashes()` ile basilan SERBEST METIN kolonlari. Slug, e-posta, IP,
     * user-agent gibi hic kacis gormemis kolonlar BILEREK disarida.
     *
     * Var olmayan tablo/kolonlar Schema kontrolu ile sessizce atlanir; komut
     * semasi farkli kurulumlarda da calisir.
     *
     * @var array<string, list<string>>
     */
    private const MAP = [
        'posts' => ['title', 'content', 'meta_description', 'meta_keywords'],
        'post_histories' => ['title', 'content'],
        'categories' => ['name', 'meta_description', 'meta_keywords'],
        'comments' => ['name', 'comment'],
        'contact_pages' => ['title', 'description', 'meta_description', 'meta_keywords'],
        'seo_settings' => ['site_name', 'title', 'description', 'keywords', 'author'],
        'menu_items' => ['title'],
        'users' => ['name', 'surname', 'nickname', 'about'],
    ];

    public function handle(): int
    {
        $write = (bool) $this->option('force');
        $chunk = max(50, (int) $this->option('chunk'));
        $sampleLimit = max(0, (int) $this->option('samples'));
        $only = (array) $this->option('table');

        if ($write) {
            $this->warn('YAZMA MODU — bu islem GERI ALINAMAZ. Veritabani yedeginiz var mi?');

            if (! $this->confirm('Devam edilsin mi?', false)) {
                $this->line('Iptal edildi.');

                return self::SUCCESS;
            }
        } else {
            $this->warn('KURU CALISMA — hicbir sey yazilmayacak. Yazmak icin --force ekleyin.');
        }

        $this->newLine();

        $totalRows = 0;
        $totalCells = 0;
        $leftovers = [];

        foreach (self::MAP as $table => $columns) {
            if ($only !== [] && ! in_array($table, $only, true)) {
                continue;
            }

            if (! Schema::hasTable($table)) {
                $this->line("  <fg=gray>atlandi</> {$table} (tablo yok)");

                continue;
            }

            $columns = array_values(array_filter(
                $columns,
                static fn (string $column): bool => Schema::hasColumn($table, $column)
            ));

            if ($columns === []) {
                $this->line("  <fg=gray>atlandi</> {$table} (eslesen kolon yok)");

                continue;
            }

            [$rows, $cells, $samples, $stillEscaped] = $this->process($table, $columns, $chunk, $write, $sampleLimit);

            $totalRows += $rows;
            $totalCells += $cells;

            if ($stillEscaped > 0) {
                $leftovers[$table] = $stillEscaped;
            }

            $verb = $write ? 'guncellendi' : 'guncellenecek';
            $style = $rows > 0 ? 'fg=yellow' : 'fg=gray';

            $this->line("  <{$style}>{$table}</> — {$rows} satir {$verb}, {$cells} alan (".implode(', ', $columns).')');

            foreach ($samples as $sample) {
                $this->line('      <fg=red>-</> '.$sample['before']);
                $this->line('      <fg=green>+</> '.$sample['after']);
            }
        }

        $this->newLine();
        $this->info(($write ? 'Bitti: ' : 'Ozet: ')."{$totalRows} satir, {$totalCells} alan.");

        if ($leftovers !== []) {
            $this->newLine();
            $this->warn('Bir gecisten SONRA hala kacisli tirnak iceren satirlar var:');

            foreach ($leftovers as $table => $count) {
                $this->line("  {$table}: {$count} alan");
            }

            $this->line('Bu icerik birden fazla kez kacislanmis — editor kacisli metni gosterip tekrar kaydettikce birikiyordu.');
            $this->line('Komutu tekrar calistirmak bir kat daha cozer, ama once orneklere BAKIN: her calistirma bir kat siler.');
        }

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $columns
     * @return array{0:int,1:int,2:list<array{before:string,after:string}>,3:int}
     */
    private function process(string $table, array $columns, int $chunk, bool $write, int $sampleLimit): array
    {
        if (! Schema::hasColumn($table, 'id')) {
            $this->line("  <fg=gray>atlandi</> {$table} (birincil anahtar 'id' yok)");

            return [0, 0, [], 0];
        }

        $rowCount = 0;
        $cellCount = 0;
        $samples = [];
        $stillEscaped = 0;

        /*
         * Eloquent DEGIL, query builder.
         *
         * `Posts` modelinin observer'i her guncellemede `post_histories`
         * tablosuna yeni bir satir yaziyor. Model uzerinden gidilseydi temizlik,
         * temizlemesi gereken veriyi cogaltirdi.
         */
        DB::table($table)
            ->select(array_merge(['id'], $columns))
            ->orderBy('id')
            ->chunkById($chunk, function ($records) use ($table, $columns, $write, $sampleLimit, &$rowCount, &$cellCount, &$samples, &$stillEscaped) {
                foreach ($records as $record) {
                    $changes = [];

                    foreach ($columns as $column) {
                        $value = $record->{$column};

                        if (! is_string($value) || $value === '' || ! str_contains($value, '\\')) {
                            continue;
                        }

                        $clean = self::unescape($value);

                        if ($clean === $value) {
                            continue;
                        }

                        $changes[$column] = $clean;

                        if (count($samples) < $sampleLimit) {
                            $samples[] = [
                                'before' => self::excerpt($value),
                                'after' => self::excerpt($clean),
                            ];
                        }

                        if (str_contains($clean, "\\'") || str_contains($clean, '\\"')) {
                            $stillEscaped++;
                        }
                    }

                    if ($changes === []) {
                        continue;
                    }

                    $rowCount++;
                    $cellCount += count($changes);

                    if ($write) {
                        DB::table($table)->where('id', $record->id)->update($changes);
                    }
                }
            });

        return [$rowCount, $cellCount, $samples, $stillEscaped];
    }

    /**
     * `addslashes()`'in tersi — AMA yalnizca onun urettigi diziler icin.
     *
     * Ters bolu bir tirnaktan ya da baska bir ters boludan onceyse kacistir ve
     * kaldirilir. Baska her durumda (`\n`, `\d`, `C:\Users`) OLDUGU GIBI kalir.
     * Tek gecis oldugu icin cozulen ciktinin uzerinden tekrar gecilmez.
     */
    public static function unescape(string $value): string
    {
        $out = '';
        $length = strlen($value);

        for ($i = 0; $i < $length; $i++) {
            $char = $value[$i];

            if ($char === '\\' && $i + 1 < $length) {
                $next = $value[$i + 1];

                if ($next === "'" || $next === '"' || $next === '\\') {
                    $out .= $next;
                    $i++;

                    continue;
                }
            }

            $out .= $char;
        }

        return $out;
    }

    private static function excerpt(string $value): string
    {
        $flat = preg_replace('/\s+/u', ' ', strip_tags($value)) ?? $value;

        return mb_strimwidth(trim($flat), 0, 110, '…');
    }
}
