<?php

namespace Database\Seeders;

use App\Models\Languages;
use App\Models\Settings\AdvertiseSettings;
use App\Models\Settings\AnalyticsSettings;
use App\Models\Settings\GeneralSettings;
use App\Models\Settings\SeoSettings;
use App\Models\Settings\SocialSettings;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Languages::create([
            'name' => 'English',
            'flag' => 'us',
            'code' => 'en',
            'is_default' => false,
            'is_active' => true,
        ]);
        Languages::create([
            'name' => 'Türkçe',
            'flag' => 'tr',
            'code' => 'tr',
            'is_default' => true,
            'is_active' => true,
        ]);
        SeoSettings::create([
            'title' => 'Laravel',
            'description' => 'Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel attempts to take the pain out of development by easing common tasks used in most web projects.',
            'keywords' => ['laravel', 'php', 'framework', 'web', 'artisans'],
            'author' => 'Laravel',
            'robots' => 'index, follow',
            'language' => 'en',
        ]);
        SeoSettings::create([
            'title' => 'Laravel',
            'description' => 'Laravel, ifade edici ve zarif sözdizimi olan bir web uygulama çerçevesidir. Geliştirmenin gerçekten tatmin edici olabilmesi için keyifli ve yaratıcı bir deneyim olması gerektiğine inanıyoruz. Laravel, çoğu web projesinde kullanılan yaygın görevleri kolaylaştırarak geliştirmenin acısını hafifletmeye çalışır.',
            'keywords' => ['laravel', 'php', 'web'],
            'author' => 'Laravel',
            'robots' => 'index, follow',
            'language' => 'tr',
        ]);

        GeneralSettings::create([
            'logo' => null,
            'favicon' => null,
            'contact_email' => null
        ]);

        AdvertiseSettings::create([
            'google_ad_manager' => null,
            'square_display_advertise' => null,
            'vertical_display_advertise' => null,
            'horizontal_display_advertise' => null,
        ]);

        AnalyticsSettings::create([
            'google_analytics' => null,
            'yandex_metrica' => null,
            'fb_pixel' => null,
            'log_rocket' => null
        ]);

        SocialSettings::create([
            'social_networks_header' => [],
            'social_networks_footer' => []
        ]);
    }
}
