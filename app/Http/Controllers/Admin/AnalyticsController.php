<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;

class AnalyticsController extends Controller
{
    public function index(Request $request){
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

    public function fetchAnalytics(Request $request){
        return response()->json($this->extracted($request));
    }

    /**
     * @param Request $request
     * @return array
     */
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

        if (file_exists(storage_path() . '/app/analytics/service-account-credentials.json')) {
            $analytics = new Analytics;
            $dashboard = [
                'viewData' => $analytics::fetchMostVisitedPages($period, maxResults: 10),
                'operatingSystem' => $analytics::fetchTopOperatingSystems($period),
                'topCountries' => $analytics::fetchTopCountries($period),
                'topBrowsers' => $analytics::fetchTopBrowsers($period),
                'events' => $analytics::get(
                    $period,
                    metrics: [
                        'publisherAdImpressions',
                        'publisherAdClicks',
                        'sessions',
                        'screenPageViews',
                        'userEngagementDuration',
                    ],
                    dimensions: [
                        'eventName',
                        'platform',
                        'region',
                    ],
                ),
                'TotalVisitorsAndPageViews' => $analytics::fetchTotalVisitorsAndPageViews($period),
                'user_types' => $analytics::fetchUserTypes($period),
            ];
        }
        else {
            $dashboard = [
                'viewData' => [],
                'operatingSystem' => [],
                'topCountries' => [],
                'topBrowsers' => [],
                'events' => [],
                'TotalVisitorsAndPageViews' => [],
                'user_types' => [],
            ];
        }

        return array_merge($dashboard, [
            'date_range' => $start_date->format('m/d/Y') . ' - ' . $end_date->format('m/d/Y'),
        ]);
    }
}
