<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Languages;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;

class DashboardController extends Controller
{
    public function index()
    {
        if (file_exists(storage_path().'/app/analytics/service-account-credentials.json')) {
            $analytics = new Analytics;
            $period = Period::days(7);
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
        } else {
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

        return view('panel.dashboard', $dashboard);
    }

    public function changeLanguage($language)
    {
        $languages = new Languages;
        $language = $languages->getLanguage($language);

        session()->put('language', $language?->code);
        session()->put('language_flag', $language?->flag);
        session()->put('language_name', $language?->name);

        app()->setLocale($language?->code);
        setlocale(LC_ALL, $language?->code);
        setlocale(LC_TIME, $language?->code);

        return redirect()->back();
    }
}
