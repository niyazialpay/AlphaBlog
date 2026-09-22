<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;
use Throwable;

/**
 * Web Push icin VAPID anahtar cifti uretir.
 *
 * Anahtar cifti SITE BASINA bir kez uretilir ve DEGISMEZ. Degistirilirse
 * mevcut tum abonelikler gecersiz olur; kullanicilarin her cihazda yeniden
 * abone olmasi gerekir. Bu yuzden komut, `.env` dosyasini kendisi YAZMAZ —
 * degerleri ekrana basar, siz elle eklersiniz. Kazara uretilen yeni bir cift
 * calisan bir kurulumu sessizce bozmasin diye bilincli bir tercih.
 */
class GenerateVapidKeys extends Command
{
    protected $signature = 'webpush:vapid';

    protected $description = 'Web Push icin VAPID anahtar cifti uretir (.env degerleri ekrana basilir)';

    public function handle(): int
    {
        if (filled(config('webpush.public_key')) || filled(config('webpush.private_key'))) {
            $this->warn('Bu kurulumda zaten bir VAPID anahtari tanimli.');
            $this->line('Degistirirseniz MEVCUT TUM ABONELIKLER GECERSIZ olur; herkesin yeniden abone olmasi gerekir.');

            if (! $this->confirm('Yine de yeni bir cift uretilsin mi?', false)) {
                $this->line('Iptal edildi.');

                return self::SUCCESS;
            }
        }

        try {
            $keys = VAPID::createVapidKeys();
        } catch (Throwable $exception) {
            $this->error('Anahtar uretilemedi: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Asagidaki satirlari .env dosyaniza ekleyin:');
        $this->newLine();
        $this->line('VAPID_SUBJECT="'.(config('webpush.subject') ?: config('app.url')).'"');
        $this->line('VAPID_PUBLIC_KEY="'.$keys['publicKey'].'"');
        $this->line('VAPID_PRIVATE_KEY="'.$keys['privateKey'].'"');
        $this->newLine();
        $this->line('Ardindan: php artisan config:clear');
        $this->newLine();

        return self::SUCCESS;
    }
}
