<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DashboardWidget;
use App\Models\Languages;
use App\Services\DashboardWidgetService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $widgets = $user->dashboardWidgets;
        $widgetData = (new DashboardWidgetService)->getDataForWidgets($widgets);
        $widgetGroups = DashboardWidgetService::widgetGroups();

        return view('panel.dashboard', compact('widgets', 'widgetData', 'widgetGroups'));
    }

    public function saveWidgets(Request $request): JsonResponse
    {
        $validated = $request->validate(['layout' => 'required|array']);
        $userId = Auth::id();

        $allowedTypes = array_keys(DashboardWidgetService::allWidgets());

        DashboardWidget::where('user_id', $userId)->delete();

        foreach ($validated['layout'] as $item) {
            if (! in_array($item['type'] ?? '', $allowedTypes, true)) {
                continue;
            }

            DashboardWidget::create([
                'user_id' => $userId,
                'widget_type' => $item['type'],
                'gs_x' => (int) ($item['x'] ?? 0),
                'gs_y' => (int) ($item['y'] ?? 0),
                'gs_w' => (int) ($item['w'] ?? 3),
                'gs_h' => (int) ($item['h'] ?? 2),
            ]);
        }

        return response()->json(['status' => 'success']);
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
