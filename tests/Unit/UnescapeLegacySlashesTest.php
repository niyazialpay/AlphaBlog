<?php

namespace Tests\Unit;

use App\Console\Commands\UnescapeLegacySlashes;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * `db:unescape-slashes` komutunun cozme kuralini kilitler.
 *
 * Kritik olan sey KALDIRILMAYANLAR: bu bir yazilim blogu, icerikte kod ornegi,
 * Windows dosya yolu ve regex var. PHP'nin `stripslashes()` fonksiyonu ters
 * boluyu hangi karakterden once olursa olsun sildigi icin kullanilamiyor —
 * "C:\Users" -> "C:Users" olurdu. Bu testler o farki korur.
 */
class UnescapeLegacySlashesTest extends TestCase
{
    private const BS = '\\';

    private const QUOTE = "'";

    private const DQUOTE = '"';

    /**
     * @return array<string, array{0:string, 1:string}>
     */
    public static function cases(): array
    {
        $b = self::BS;
        $q = self::QUOTE;
        $d = self::DQUOTE;

        return [
            'tirnak onundeki kacis kalkar' => ["C#{$b}{$q}ta", "C#{$q}ta"],
            'cift tirnak kacisi kalkar' => ["say {$b}{$d}hi{$b}{$d}", "say {$d}hi{$d}"],
            'cift ters bolu teke iner' => ["{$b}{$b}{$b}{$b}", "{$b}{$b}"],
            'kacisli ters bolu artı gercek tirnak' => ["{$b}{$b}{$b}{$q}", "{$b}{$q}"],

            // Korunmasi gerekenler — regresyon olursa icerik bozulur.
            'windows yolu korunur' => ["C:{$b}Users{$b}Admin", "C:{$b}Users{$b}Admin"],
            'regex korunur' => ["{$b}d+{$b}s*", "{$b}d+{$b}s*"],
            'php namespace korunur' => ["App{$b}Models{$b}Post", "App{$b}Models{$b}Post"],
            'satir sonu kacisi korunur' => ["ilk{$b}nikinci", "ilk{$b}nikinci"],

            'ters bolusuz metin degismez' => ['Merhaba dunya', 'Merhaba dunya'],
            'bos dize' => ['', ''],
            'sondaki yalniz ters bolu korunur' => ["son{$b}", "son{$b}"],
        ];
    }

    #[Test]
    #[DataProvider('cases')]
    public function it_only_undoes_addslashes_sequences(string $input, string $expected): void
    {
        $this->assertSame($expected, UnescapeLegacySlashes::unescape($input));
    }

    #[Test]
    public function it_is_the_exact_inverse_of_addslashes(): void
    {
        $originals = [
            "C#'ta Veritabani Baglantisi",
            'PHP\'de "Fonksiyon" Hazirlama',
            'Yol: C:\\Users\\Admin ve regex \\d+',
            'namespace App\\Models; // \'tek tirnak\'',
        ];

        foreach ($originals as $original) {
            $this->assertSame(
                $original,
                UnescapeLegacySlashes::unescape(addslashes($original)),
                'addslashes ile kacislanan metin birebir geri gelmeli.'
            );
        }
    }

    #[Test]
    public function one_pass_removes_exactly_one_level_of_escaping(): void
    {
        $original = "C#'ta";
        $doubled = addslashes(addslashes($original));

        $once = UnescapeLegacySlashes::unescape($doubled);

        $this->assertSame(addslashes($original), $once, 'Tek gecis yalnizca bir kat cozmeli.');
        $this->assertSame($original, UnescapeLegacySlashes::unescape($once), 'Ikinci gecis kalani cozmeli.');
    }
}
