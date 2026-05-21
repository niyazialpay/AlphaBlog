<?php

namespace App\Services;

use App\Models\Firewall\FirewallLogs;
use App\Models\Post\Comments;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;

class DashboardWidgetService
{
    public const WIDGETS = [
        'ga4_active_users' => ['label' => 'Aktif Kullanıcı',      'w' => 3, 'h' => 2, 'group' => 'GA4 Metrikler'],
        'ga4_new_users' => ['label' => 'Yeni Kullanıcı',       'w' => 3, 'h' => 2, 'group' => 'GA4 Metrikler'],
        'ga4_pageviews' => ['label' => 'Sayfa Görüntüleme',    'w' => 3, 'h' => 2, 'group' => 'GA4 Metrikler'],
        'ga4_events' => ['label' => 'Etkinlik Sayısı',      'w' => 3, 'h' => 2, 'group' => 'GA4 Metrikler'],
        'ga4_engagement_time' => ['label' => 'Ort. Etkileşim',       'w' => 3, 'h' => 2, 'group' => 'GA4 Metrikler'],
        'ga4_visitors_trend' => ['label' => 'Ziyaretçi Trendi',     'w' => 6, 'h' => 4, 'group' => 'GA4 Grafikler'],
        'ga4_browsers' => ['label' => 'Tarayıcılar',          'w' => 3, 'h' => 4, 'group' => 'GA4 Grafikler'],
        'ga4_countries' => ['label' => 'Ülkeler',              'w' => 3, 'h' => 4, 'group' => 'GA4 Grafikler'],
        'ga4_os' => ['label' => 'İşletim Sistemleri',   'w' => 3, 'h' => 4, 'group' => 'GA4 Grafikler'],
        'ga4_user_types' => ['label' => 'Kullanıcı Tipi',       'w' => 3, 'h' => 4, 'group' => 'GA4 Grafikler'],
        'ga4_top_pages' => ['label' => 'En Çok Görüntülenen',  'w' => 6, 'h' => 4, 'group' => 'GA4 Grafikler'],
        'gsc_clicks' => ['label' => 'GSC Tıklama',          'w' => 3, 'h' => 2, 'group' => 'Search Console'],
        'gsc_impressions' => ['label' => 'GSC Gösterim',         'w' => 3, 'h' => 2, 'group' => 'Search Console'],
        'gsc_ctr' => ['label' => 'GSC CTR',              'w' => 3, 'h' => 2, 'group' => 'Search Console'],
        'gsc_position' => ['label' => 'GSC Ort. Konum',       'w' => 3, 'h' => 2, 'group' => 'Search Console'],
        'gsc_keywords' => ['label' => 'Top Keywords',         'w' => 12, 'h' => 5, 'group' => 'Search Console'],
        'site_comments' => ['label' => 'Son Yorumlar',         'w' => 6, 'h' => 4, 'group' => 'Site'],
        'site_firewall' => ['label' => 'Firewall Logları',     'w' => 6, 'h' => 4, 'group' => 'Site'],
    ];

    public function getDataForWidgets(Collection $widgets): array
    {
        $types = $widgets->pluck('widget_type')->unique()->toArray();
        $hasGa4 = collect($types)->contains(fn ($t) => str_starts_with($t, 'ga4_'));
        $hasGsc = collect($types)->contains(fn ($t) => str_starts_with($t, 'gsc_'));

        $data = [
            'ga4' => [],
            'gsc' => ['performance' => [], 'keywords' => [], 'trend' => []],
            'comments' => [],
            'firewall' => [],
        ];

        if ($hasGa4 && file_exists(storage_path('app/analytics/service-account-credentials.json'))) {
            $period = Period::days(7);
            $end = Carbon::now();
            $start = Carbon::now()->subDays(7);

            $cacheKey = config('cache.prefix').'dashboard_widgets_ga4_'.auth()->id();
            $data['ga4'] = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($period, $start, $end) {
                $overview = $this->fetchGA4Overview($start, $end);

                return [
                    'overview' => $overview,
                    'trend' => Analytics::fetchTotalVisitorsAndPageViews($period),
                    'browsers' => Analytics::fetchTopBrowsers($period),
                    'countries' => Analytics::fetchTopCountries($period),
                    'os' => Analytics::fetchTopOperatingSystems($period),
                    'user_types' => Analytics::fetchUserTypes($period),
                    'top_pages' => Analytics::fetchMostVisitedPages($period, maxResults: 10),
                ];
            });
        }

        if ($hasGsc) {
            $end = Carbon::now();
            $start = Carbon::now()->subDays(7);
            $cacheKey = config('cache.prefix').'dashboard_widgets_gsc_'.auth()->id();
            $data['gsc'] = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($start, $end) {
                $service = new GoogleSearchConsoleService;

                return [
                    'performance' => $service->getPerformance($start, $end),
                    'keywords' => $service->getKeywords($start, $end, limit: 10),
                    'trend' => $service->getClicksTrend($start, $end),
                ];
            });
        }

        $data['comments'] = Comments::with('user', 'post')->orderBy('created_at', 'desc')->limit(5)->get();
        $data['firewall'] = FirewallLogs::orderBy('created_at', 'desc')->limit(5)->get();

        return $data;
    }

    private function fetchGA4Overview(Carbon $start, Carbon $end): array
    {
        try {
            $period = Period::create($start, $end);
            $result = Analytics::get($period, ['activeUsers', 'newUsers', 'screenPageViews', 'eventCount', 'userEngagementDuration', 'sessions']);
            if ($result->isEmpty()) {
                return [];
            }
            $row = $result->first();
            $currUsers = (int) ($row['activeUsers'] ?? 0);
            $currNew = (int) ($row['newUsers'] ?? 0);
            $currViews = (int) ($row['screenPageViews'] ?? 0);
            $currEvents = (int) ($row['eventCount'] ?? 0);
            $currDur = (float) ($row['userEngagementDuration'] ?? 0);
            $currSessions = (int) ($row['sessions'] ?? 1);

            // Previous period
            $diffDays = $start->diffInDays($end);
            $prevEnd = $start->copy()->subDay();
            $prevStart = $prevEnd->copy()->subDays($diffDays);
            $prevResult = Analytics::get(Period::create($prevStart, $prevEnd), ['activeUsers', 'newUsers', 'screenPageViews', 'eventCount', 'userEngagementDuration', 'sessions']);
            $prevRow = $prevResult->first() ?? [];
            $prevUsers = (int) ($prevRow['activeUsers'] ?? 0);
            $prevNew = (int) ($prevRow['newUsers'] ?? 0);
            $prevViews = (int) ($prevRow['screenPageViews'] ?? 0);
            $prevEvents = (int) ($prevRow['eventCount'] ?? 0);
            $prevDur = (float) ($prevRow['userEngagementDuration'] ?? 0);
            $prevSessions = (int) ($prevRow['sessions'] ?? 1);

            $pctChange = fn ($c, $p) => $p > 0 ? round(($c - $p) / $p * 100, 1) : null;
            $engTime = fn ($dur, $sess) => $sess > 0 ? gmdate('i\m s\s', (int) ($dur / $sess)) : '0m 0s';

            return [
                'active_users' => ['value' => $currUsers,                                                             'change' => $pctChange($currUsers, $prevUsers)],
                'new_users' => ['value' => $currNew,                                                               'change' => $pctChange($currNew, $prevNew)],
                'pageviews' => ['value' => $currViews,                                                             'change' => $pctChange($currViews, $prevViews)],
                'events' => ['value' => $currEvents,                                                            'change' => $pctChange($currEvents, $prevEvents)],
                'engagement_time' => ['value' => $engTime($currDur, $currSessions), 'change' => $pctChange($currDur / max($currSessions, 1), $prevDur / max($prevSessions, 1))],
            ];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function widgetGroups(): array
    {
        $groups = [];
        foreach (self::WIDGETS as $type => $config) {
            $groups[$config['group']][$type] = $config;
        }

        return $groups;
    }
}
