<?php

namespace Tests\Unit\Models;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AppModelsTest extends TestCase
{
    #[DataProvider('modelProvider')]
    public function test_app_model_is_instantiable_and_has_table(string $modelClass): void
    {
        $model = new $modelClass;

        $this->assertInstanceOf(Model::class, $model);
        $this->assertIsString($model->getTable());
        $this->assertNotSame('', trim($model->getTable()));
    }

    #[DataProvider('castsProvider')]
    public function test_app_model_declares_expected_casts(string $modelClass, array $expectedCasts): void
    {
        $casts = (new $modelClass)->getCasts();

        foreach ($expectedCasts as $attribute => $castType) {
            $this->assertArrayHasKey($attribute, $casts);
            $this->assertSame($castType, $casts[$attribute]);
        }
    }

    public static function modelProvider(): array
    {
        return [
            [\App\Models\AdminOneSignal::class],
            [\App\Models\Cloudflare::class],
            [\App\Models\ContactMessages::class],
            [\App\Models\ContactPage::class],
            [\App\Models\Firewall\Firewall::class],
            [\App\Models\Firewall\FirewallLogs::class],
            [\App\Models\IPFilter\IPFilter::class],
            [\App\Models\IPFilter\IPList::class],
            [\App\Models\IPFilter\RouteList::class],
            [\App\Models\Languages::class],
            [\App\Models\Logs::class],
            [\App\Models\Menu\Menu::class],
            [\App\Models\Menu\MenuItems::class],
            [\App\Models\OneSignal::class],
            [\App\Models\PersonalNotes\PersonalNoteCategories::class],
            [\App\Models\PersonalNotes\PersonalNotes::class],
            [\App\Models\Post\Categories::class],
            [\App\Models\Post\Comments::class],
            [\App\Models\Post\PostCategory::class],
            [\App\Models\Post\PostHistory::class],
            [\App\Models\Post\Posts::class],
            [\App\Models\ProfilePrivacy::class],
            [\App\Models\RouteRedirects::class],
            [\App\Models\Search::class],
            [\App\Models\Session::class],
            [\App\Models\Settings\AdvertiseSettings::class],
            [\App\Models\Settings\AnalyticsSettings::class],
            [\App\Models\Settings\GeneralSettings::class],
            [\App\Models\Settings\SeoSettings::class],
            [\App\Models\Settings\SocialSettings::class],
            [\App\Models\SocialNetworks::class],
            [\App\Models\Themes::class],
            [\App\Models\User::class],
            [\App\Models\UserSessions::class],
            [\App\Models\WebAuthnCredential::class],
        ];
    }

    public static function castsProvider(): array
    {
        return [
            [
                \App\Models\Cloudflare::class,
                [
                    'cf_email' => 'encrypted',
                    'cf_key' => 'encrypted',
                    'domain' => 'string',
                ],
            ],
            [
                \App\Models\OneSignal::class,
                [
                    'app_id' => 'encrypted',
                    'auth_key' => 'encrypted',
                ],
            ],
            [
                \App\Models\PersonalNotes\PersonalNoteCategories::class,
                [
                    'name' => 'encrypted',
                ],
            ],
            [
                \App\Models\PersonalNotes\PersonalNotes::class,
                [
                    'content' => 'encrypted',
                ],
            ],
            [
                \App\Models\Session::class,
                [
                    'last_activity' => 'datetime',
                ],
            ],
            [
                \App\Models\User::class,
                [
                    'email_verified_at' => 'datetime',
                    'password' => 'hashed',
                ],
            ],
        ];
    }
}
