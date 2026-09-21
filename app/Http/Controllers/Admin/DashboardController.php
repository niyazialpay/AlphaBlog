<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DashboardWidget;
use App\Models\Languages;
use App\Services\DashboardWidgetService;
use App\Support\Panel\PanelResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class DashboardController extends Controller
{
    public function index(): SymfonyResponse
    {
        $user = Auth::user();
        $widgets = $user->dashboardWidgets;
        $widgetData = (new DashboardWidgetService)->getDataForWidgets($widgets);
        $widgetGroups = DashboardWidgetService::widgetGroups();

        return PanelResponse::render(
            'Dashboard/Index',
            'panel.dashboard',
            [
                'widgets' => $widgets->map(fn (DashboardWidget $widget) => [
                    'id' => $widget->id,
                    'type' => $widget->widget_type,
                    'x' => (int) $widget->gs_x,
                    'y' => (int) $widget->gs_y,
                    'w' => (int) $widget->gs_w,
                    'h' => (int) $widget->gs_h,
                ])->values(),
                'widgetGroups' => $widgetGroups,
                /*
                 * `comments` ve `firewall` Eloquent koleksiyonlari; blade
                 * icinde `$comment->user?->name` / `diffForHumans()` cagriliyordu.
                 * Inertia prop'u olarak duz diziye indirilir, tarihler ISO-8601.
                 */
                'widgetData' => [
                    'ga4' => $widgetData['ga4'],
                    'gsc' => $widgetData['gsc'],
                    /*
                     * Widget'in "veri yok" ile "yapilandirilmamis" / "istek
                     * patladi" arasini ayirabilmesi icin kaynak basina durum;
                     * `settingsUrl` de yapilandirilmamis GSC widget'inin
                     * kullaniciyi dogru sekmeye yollamasi icin.
                     */
                    'status' => $widgetData['status'],
                    'settingsUrl' => route('admin.settings', ['tab' => 'seo']),
                    'comments' => collect($widgetData['comments'])->map(fn ($comment) => [
                        'id' => $comment->id,
                        'author' => $comment->user?->name ?? 'Anonim',
                        'comment' => Str::limit($comment->comment ?? '', 60),
                        'createdAt' => $comment->created_at?->toIso8601String(),
                    ])->values(),
                    'firewall' => collect($widgetData['firewall'])->map(fn ($log) => [
                        'id' => $log->id,
                        'ip' => $log->ip,
                        'reason' => $log->reason,
                        'createdAt' => $log->created_at?->toIso8601String(),
                    ])->values(),
                ],
            ],
            compact('widgets', 'widgetData', 'widgetGroups'),
        );
    }

    public function saveWidgets(Request $request): RedirectResponse
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

        return back()->with('success', __('general.saved'));
    }

    public function changeLanguage($language): RedirectResponse
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
