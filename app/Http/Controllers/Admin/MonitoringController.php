<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function showPulse(): Application|View|Factory|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\View
    {
        abort_unless(auth()->user()?->can('viewPulse'), 403);

        return view('panel.monitoring', [
            'iframe_url' => route('pulse'),
            'title' => 'Pulse Monitoring',
        ]);
    }

    public function showLogs(): Application|View|Factory|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\View
    {
        abort_unless(auth()->user()?->can('viewPulse'), 403);

        return view('panel.monitoring', [
            'iframe_url' => config('app.url').config('log-viewer.route_path'),
            'title' => 'Logs',
        ]);
    }

    public function showTelescope(): Application|View|Factory|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\View
    {
        abort_unless(auth()->user()?->can('viewPulse'), 403);

        return view('panel.monitoring', [
            'iframe_url' => route('telescope'),
            'title' => 'Telescope Monitoring',
        ]);
    }

    public function showHorizon(): Application|View|Factory|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\View
    {
        abort_unless(auth()->user()?->can('viewPulse'), 403);

        return view('panel.monitoring', [
            'iframe_url' => config('app.url').'/'.config('horizon.path'),
            'title' => 'Horizon Monitoring',
        ]);
    }
}
