<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Settings\GeneralSettings;
use App\Services\GoogleSearchConsoleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class SearchConsoleController extends Controller
{
    public function index(Request $request): View
    {
        return view('panel.search-console', $this->getData($request));
    }

    public function fetch(Request $request): JsonResponse
    {
        return response()->json($this->getData($request));
    }

    private function getData(Request $request): array
    {
        if ($request->has('date_range')) {
            $parts = explode(' - ', $request->date_range);
            $startDate = Carbon::createFromFormat('m/d/Y', $parts[0]);
            $endDate = Carbon::createFromFormat('m/d/Y', $parts[1]);
        } else {
            $endDate = Carbon::now();
            $startDate = Carbon::now()->subDays(28);
        }

        $dateRange = $startDate->format('m/d/Y').' - '.$endDate->format('m/d/Y');

        $credPath = storage_path('app/analytics/service-account-credentials.json');
        $siteUrl = GeneralSettings::first()?->google_indexing_site_url;

        if (! file_exists($credPath) || ! $siteUrl) {
            return [
                'configured' => false,
                'date_range' => $dateRange,
                'performance' => [],
                'keywords' => [],
                'trend' => [],
            ];
        }

        $cacheKey = config('cache.prefix').'gsc_data_'.$startDate->format('Y-m-d').'_'.$endDate->format('Y-m-d');

        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($startDate, $endDate) {
            $service = new GoogleSearchConsoleService;

            return [
                'performance' => $service->getPerformance($startDate, $endDate),
                'keywords' => $service->getKeywords($startDate, $endDate),
                'trend' => $service->getClicksTrend($startDate, $endDate),
            ];
        });

        return array_merge($data, [
            'configured' => true,
            'date_range' => $dateRange,
        ]);
    }
}
