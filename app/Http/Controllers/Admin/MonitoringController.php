<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Panel\PanelResponse;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class MonitoringController extends Controller
{
    private static function namedRoute(string $name): ?string
    {
        return Route::has($name) ? route($name) : null;
    }

    public function showPulse(): Response
    {
        abort_unless(auth()->user()?->can('viewPulse'), 403);

        return PanelResponse::render(
            'Monitoring/Index',
            'panel.monitoring',
            ['iframeUrl' => self::namedRoute('pulse'), 'title' => 'Pulse Monitoring'],
            ['iframe_url' => self::namedRoute('pulse'), 'title' => 'Pulse Monitoring'],
        );
    }

    public function showLogs(): Response
    {
        abort_unless(auth()->user()?->can('viewPulse'), 403);

        return PanelResponse::render(
            'Monitoring/Index',
            'panel.monitoring',
            ['iframeUrl' => config('app.url').config('log-viewer.route_path'), 'title' => 'Logs'],
            ['iframe_url' => config('app.url').config('log-viewer.route_path'), 'title' => 'Logs'],
        );
    }

    public function showTelescope(): Response
    {
        abort_unless(auth()->user()?->can('viewTelescope'), 403);

        return PanelResponse::render(
            'Monitoring/Index',
            'panel.monitoring',
            ['iframeUrl' => self::namedRoute('telescope'), 'title' => 'Telescope Monitoring'],
            ['iframe_url' => self::namedRoute('telescope'), 'title' => 'Telescope Monitoring'],
        );
    }

    public function showHorizon(): Response
    {
        abort_unless(auth()->user()?->can('viewHorizon'), 403);

        return PanelResponse::render(
            'Monitoring/Index',
            'panel.monitoring',
            ['iframeUrl' => config('app.url').'/'.config('horizon.path'), 'title' => 'Horizon Monitoring'],
            ['iframe_url' => config('app.url').'/'.config('horizon.path'), 'title' => 'Horizon Monitoring'],
        );
    }
}
