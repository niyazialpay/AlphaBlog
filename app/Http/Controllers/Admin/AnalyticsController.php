<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('date_range')) {
            $date_range = explode(' - ', $request->date_range);
            $start_date = Carbon::createFromDate($date_range[0]);
            $end_date = Carbon::createFromDate($date_range[1]);
        } else {
            $end_date = Carbon::now();
            $start_date = Carbon::now()->subDays(7);
        }

        return view('panel.analytics', $this->extracted($request));
    }

    public function fetchAnalytics(Request $request)
    {
        return response()->json($this->extracted($request));
    }

    public function extracted(Request $request): array
    {
        if ($request->has('date_range')) {
            $date_range = explode(' - ', $request->date_range);
            $start_date = Carbon::createFromDate($date_range[0]);
            $end_date = Carbon::createFromDate($date_range[1]);
        } else {
            $end_date = Carbon::now();
            $start_date = Carbon::now()->subDays(7);
        }
        $period = Period::create($start_date, $end_date);

        if (file_exists(storage_path().'/app/analytics/service-account-credentials.json')) {
            $analytics = new Analytics;
            $dashboard = [
                'viewData' => $analytics::fetchMostVisitedPages($period, maxResults: 10),
                'operatingSystem' => $analytics::fetchTopOperatingSystems($period),
                'topCountries' => $analytics::fetchTopCountries($period),
                'topBrowsers' => $analytics::fetchTopBrowsers($period),
                'TotalVisitorsAndPageViews' => $analytics::fetchTotalVisitorsAndPageViews($period),
                'user_types' => $analytics::fetchUserTypes($period),
                'overview' => $this->fetchOverviewMetrics($start_date, $end_date),
                'trend' => $this->fetchTrendData($start_date, $end_date),
            ];
        } else {
            $dashboard = [
                'viewData' => [],
                'operatingSystem' => [],
                'topCountries' => [],
                'topBrowsers' => [],
                'TotalVisitorsAndPageViews' => [],
                'user_types' => [],
                'overview' => [],
                'trend' => ['current' => [], 'previous' => []],
            ];
        }

        return array_merge($dashboard, [
            'date_range' => $start_date->format('m/d/Y').' - '.$end_date->format('m/d/Y'),
        ]);
    }

    private function fetchOverviewMetrics(Carbon $start, Carbon $end): array
    {
        $cacheKey = config('cache.prefix').'analytics_overview_'.$start->format('Y-m-d').'_'.$end->format('Y-m-d');

        return cache()->remember($cacheKey, now()->addMinutes(15), function () use ($start, $end) {
            $period = Period::create($start, $end);
            $rows = Analytics::get($period, ['activeUsers', 'newUsers', 'screenPageViews', 'eventCount', 'userEngagementDuration', 'sessions']);
            $curr = $rows->first() ?? [];

            $diffDays = $start->diffInDays($end);
            $prevEnd = $start->copy()->subDay();
            $prevStart = $prevEnd->copy()->subDays($diffDays);
            $prevPeriod = Period::create($prevStart, $prevEnd);
            $prevRows = Analytics::get($prevPeriod, ['activeUsers', 'newUsers', 'screenPageViews', 'eventCount', 'userEngagementDuration', 'sessions']);
            $prev = $prevRows->first() ?? [];

            $pctChange = function (int|float $current, int|float $previous): ?float {
                return $previous > 0 ? round(($current - $previous) / $previous * 100, 1) : null;
            };

            $currSessions = (int) ($curr['sessions'] ?? 0);
            $currEngagement = (float) ($curr['userEngagementDuration'] ?? 0);
            $prevSessions = (int) ($prev['sessions'] ?? 0);
            $prevEngagement = (float) ($prev['userEngagementDuration'] ?? 0);

            $engagementSeconds = $currSessions > 0 ? (int) round($currEngagement / $currSessions) : 0;
            $prevEngagementSeconds = $prevSessions > 0 ? (int) round($prevEngagement / $prevSessions) : 0;
            $engagementFormatted = floor($engagementSeconds / 60).'m '.($engagementSeconds % 60).'s';

            $currActiveUsers = (int) ($curr['activeUsers'] ?? 0);
            $currNewUsers = (int) ($curr['newUsers'] ?? 0);
            $currPageviews = (int) ($curr['screenPageViews'] ?? 0);
            $currEvents = (int) ($curr['eventCount'] ?? 0);

            $prevActiveUsers = (int) ($prev['activeUsers'] ?? 0);
            $prevNewUsers = (int) ($prev['newUsers'] ?? 0);
            $prevPageviews = (int) ($prev['screenPageViews'] ?? 0);
            $prevEvents = (int) ($prev['eventCount'] ?? 0);

            return [
                'active_users' => ['value' => $currActiveUsers, 'change' => $pctChange($currActiveUsers, $prevActiveUsers)],
                'new_users' => ['value' => $currNewUsers, 'change' => $pctChange($currNewUsers, $prevNewUsers)],
                'pageviews' => ['value' => $currPageviews, 'change' => $pctChange($currPageviews, $prevPageviews)],
                'events' => ['value' => $currEvents, 'change' => $pctChange($currEvents, $prevEvents)],
                'engagement_time' => ['value' => $engagementFormatted, 'change' => $pctChange($engagementSeconds, $prevEngagementSeconds)],
            ];
        });
    }

    private function fetchTrendData(Carbon $start, Carbon $end): array
    {
        $cacheKey = config('cache.prefix').'analytics_trend_'.$start->format('Y-m-d').'_'.$end->format('Y-m-d');

        return cache()->remember($cacheKey, now()->addMinutes(15), function () use ($start, $end) {
            $period = Period::create($start, $end);
            $currRows = Analytics::get($period, ['activeUsers'], ['date']);

            $diffDays = $start->diffInDays($end);
            $prevEnd = $start->copy()->subDay();
            $prevStart = $prevEnd->copy()->subDays($diffDays);
            $prevPeriod = Period::create($prevStart, $prevEnd);
            $prevRows = Analytics::get($prevPeriod, ['activeUsers'], ['date']);

            return [
                'current' => $currRows->map(fn ($row) => ['date' => $row['date'], 'activeUsers' => (int) $row['activeUsers']])->values()->toArray(),
                'previous' => $prevRows->map(fn ($row) => ['date' => $row['date'], 'activeUsers' => (int) $row['activeUsers']])->values()->toArray(),
            ];
        });
    }
}
