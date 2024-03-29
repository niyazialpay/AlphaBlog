<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;

class DashboardController extends Controller
{
    public function index(){
        if(file_exists(storage_path().'/app/analytics/service-account-credentials.json')){
            $analytics = new Analytics();
            $period = Period::days(7);
            $dashboard = [
                'viewData' => $analytics::fetchMostVisitedPages($period, maxResults:10),
                'operatingSystem' => $analytics::fetchTopOperatingSystems($period),
                'topCountries' => $analytics::fetchTopCountries($period),
                'topBrowsers' => $analytics::fetchTopBrowsers($period),
                'ad_impression' => $analytics::get(
                    Period::days(7),
                    metrics: ['publisherAdImpressions'],
                    dimensions: ['eventName'],
                ),
                'TotalVisitorsAndPageViews' => $analytics::fetchTotalVisitorsAndPageViews($period),
                'user_types' => $analytics::fetchUserTypes($period),
            ];
        }
        else{
            $dashboard = [
                'viewData' => [],
                'operatingSystem' => [],
                'topCountries' => [],
                'topBrowsers' => [],
                'ad_impression' => [],
                'TotalVisitorsAndPageViews' => [],
                'user_types' => [],
            ];
        }


        return view("panel.dashboard", $dashboard);
    }
}
