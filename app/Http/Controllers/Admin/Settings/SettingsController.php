<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\CloudflareApiSettingsRequest;
use App\Models\Cloudflare;
use App\Models\Languages;
use App\Models\OneSignal;
use App\Models\Settings\AdvertiseSettings;
use App\Models\Settings\AnalyticsSettings;
use App\Models\Settings\GeneralSettings;
use App\Models\Settings\SeoSettings;
use App\Models\Settings\SocialSettings;
use App\Models\SocialNetworks;
use App\Models\Themes;
use App\Support\Panel\PanelResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SettingsController extends Controller
{
    public function index(): Response
    {
        $languages = Languages::all();

        $general = self::setting('general_settings', GeneralSettings::class);
        $advertise = self::setting('ad_settings', AdvertiseSettings::class);
        $analytics = self::setting('analytic_settings', AnalyticsSettings::class);
        $socialNetworks = self::setting('social_networks', SocialNetworks::class);
        $socialSettings = self::setting('social_settings', SocialSettings::class);
        $themes = Themes::all();
        $onesignal = OneSignal::first();
        $cloudflare = Cloudflare::first();
        $robots = file_exists(public_path('robots.txt')) ? file_get_contents(public_path('robots.txt')) : null;

        return PanelResponse::render(
            'Settings/Index',
            'panel.settings.index',
            [
                'tab' => request()->get('tab', 'general'),

                'seo' => SeoSettings::all()->keyBy('language')->map(fn (SeoSettings $item) => [
                    'language' => $item->language,
                    'site_name' => $item->site_name,
                    'title' => $item->title,
                    'description' => $item->description,
                    'keywords' => $item->keywords,
                    'author' => $item->author,
                    'robots' => $item->robots,
                ]),

                'general' => self::attributes($general, [
                    'contact_email', 'sharethis', 'llms_txt_intro', 'llms_txt_instructions',
                    'google_indexing_enabled', 'google_indexing_daily_limit', 'google_indexing_site_url',
                    'homepage_featured_count', 'homepage_recent_count',
                ]),
                'logos' => [
                    'light' => $general?->getFirstMediaUrl('site_logo_light') ?: null,
                    'dark' => $general?->getFirstMediaUrl('site_logo_dark') ?: null,
                    'favicon' => $general?->getFirstMediaUrl('site_favicon') ?: null,
                    'app_icon' => $general?->getFirstMediaUrl('app_icon') ?: null,
                ],

                'advertise' => self::attributes($advertise, [
                    'google_ad_manager', 'square_display_advertise',
                    'vertical_display_advertise', 'horizontal_display_advertise',
                ]),

                'analytics' => self::attributes($analytics, [
                    'google_analytics', 'ga_measurement_id', 'ga_api_secret',
                    'yandex_metrica', 'fb_pixel', 'log_rocket',
                ]),

                'social' => self::attributes($socialNetworks, [
                    'linkedin', 'facebook', 'x', 'bluesky', 'instagram', 'github', 'devto',
                    'medium', 'youtube', 'reddit', 'xbox', 'deviantart', 'website', 'twitch',
                    'telegram', 'discord',
                ]),
                'socialDisplay' => [
                    'social_networks_header' => self::socialList($socialSettings?->social_networks_header),
                    'social_networks_footer' => self::socialList($socialSettings?->social_networks_footer),
                ],
                'socialOptions' => collect(social_list())
                    ->map(fn (string $label, string $key) => ['value' => $key, 'label' => $label])
                    ->values(),

                'languageRecords' => $languages->map(fn (Languages $item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'flag' => $item->flag,
                    'is_active' => (bool) $item->is_active,
                    'is_default' => (bool) $item->is_default,
                ])->values(),

                'themes' => $themes->map(fn (Themes $item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'is_default' => (bool) $item->is_default,
                ])->values(),

                'notifications' => [
                    'safari_web_id' => $onesignal->safari_web_id ?? null,
                    'user_segmentation' => (bool) ($onesignal->user_segmentation ?? false),
                    'has_app_id' => filled($onesignal->app_id ?? null),
                    'has_auth_key' => filled($onesignal->auth_key ?? null),
                ],

                'cloudflare' => $cloudflare ? [
                    'cf_email' => $cloudflare->cf_email,
                    'domain' => $cloudflare->domain,
                    'has_key' => filled($cloudflare->cf_key),
                ] : ['cf_email' => null, 'domain' => null, 'has_key' => false],

                'robots' => $robots,
            ],
            [
                'seo_settings' => new SeoSettings,
                'general_settings' => $general,
                'advertise_settings' => $advertise,
                'analytics_settings' => $analytics,
                'all_languages' => $languages,
                'social_networks' => $socialNetworks,
                'robots_txt' => $robots,
                'themes' => $themes,
                'social_settings' => $socialSettings,
                'onesignal' => $onesignal,
                'cloudflare' => $cloudflare,
            ],
        );
    }

    /**
     * @param  class-string  $model
     */
    private static function setting(string $binding, string $model): ?object
    {
        if (app()->bound($binding)) {
            return app($binding);
        }

        try {
            return $model::first();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  list<string>  $fields
     * @return array<string, mixed>
     */
    private static function attributes(?object $model, array $fields): array
    {
        return collect($fields)
            ->mapWithKeys(fn (string $field) => [$field => $model->{$field} ?? null])
            ->all();
    }

    /**
     * @return list<string>
     */
    private static function socialList(mixed $value): array
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        if (! is_array($value)) {
            return [];
        }

        $allowed = array_keys(social_list());

        return array_values(array_filter(
            $value,
            fn ($item) => is_string($item) && in_array($item, $allowed, true),
        ));
    }

    public function updateApiSettings(CloudflareApiSettingsRequest $request): RedirectResponse
    {
        $cf = Cloudflare::first();
        if (! $cf) {
            $cf = new Cloudflare;
        }
        $previousDomain = $cf->domain;
        $cf->cf_email = $request->post('cf_email');
        if ($request->filled('cf_key')) {
            $cf->cf_key = $request->post('cf_key');
        }
        $cf->domain = $request->post('cf_domain');
        $cf->save();

        foreach (array_filter([$previousDomain, $cf->domain]) as $domain) {
            Cache::forget(Cloudflare::zoneCacheKey($domain));
        }

        return back()->with('success', __('cloudflare.api_settings_updated'));
    }
}
