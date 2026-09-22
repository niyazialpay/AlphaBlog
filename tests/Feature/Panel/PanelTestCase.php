<?php

namespace Tests\Feature\Panel;

use App\Http\Middleware\HandlePanelInertiaRequests;
use App\Models\Firewall\Firewall;
use App\Models\IPFilter\IPFilter;
use App\Models\Languages;
use App\Models\Settings\GeneralSettings;
use App\Models\Settings\SeoSettings;
use App\Models\User;
use App\Support\Panel\PanelMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Inertia\Inertia;
use Tests\TestCase;

abstract class PanelTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected Languages $language;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Inertia::version(fn () => null);

        DB::table('languages')->insert([
            'name' => 'Türkçe',
            'code' => 'tr',
            'flag' => 'tr',
            'is_active' => true,
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->language = Languages::first();
        $languages = collect([$this->language]);

        app()->instance('default_language', $this->language);
        app()->instance('languages', $languages);

        $generalSettings = GeneralSettings::firstOrCreate([], []);

        $seoSettings = SeoSettings::firstOrCreate(
            ['language' => $this->language->code],
            ['site_name' => 'Test', 'title' => 'Test', 'description' => '', 'keywords' => '', 'author' => '', 'robots' => 'index, follow'],
        );

        app()->instance('general_settings', $generalSettings);
        app()->instance('seo_settings', $seoSettings);

        View::share('general_settings', $generalSettings);
        View::share('seo_settings', $seoSettings);
        View::share('languages', $languages);
        View::share('newCommentsCount', 0);
        View::share('searchedWordsCount', 0);

        $this->owner = User::factory()->create([
            'role' => 'owner',
            'otp' => false,
            'webauthn' => false,
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function inertiaHeaders(): array
    {
        return [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => (string) (new HandlePanelInertiaRequests)->version(request()),
            'X-Requested-With' => 'XMLHttpRequest',
        ];
    }

    protected function seedFirewall(): Firewall
    {
        $blacklist = IPFilter::create([
            'name' => 'Blacklist',
            'list_type' => 'blacklist',
            'is_active' => true,
            'route_type' => 'select',
            'code' => 403,
        ]);

        IPFilter::create([
            'name' => 'Whitelist',
            'list_type' => 'whitelist',
            'is_active' => true,
            'route_type' => 'select',
            'code' => 403,
        ]);

        return Firewall::create([
            'is_active' => false,
            'blacklist_rule_id' => $blacklist->id,
        ]);
    }

    /**
     * @param  list<string>  $routeNames
     */
    protected function migrateScreens(array $routeNames): void
    {
        config()->set('panel_inertia_routes', $routeNames);
        config()->set('settings.panel_ui', 'vue');
        config()->set('settings.panel_ui_screens', '');

        PanelMenu::flushLedger();
    }
}
