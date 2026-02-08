<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FirewallSettingsRequest;
use App\Models\Firewall\Firewall;
use App\Models\Firewall\FirewallLogs;
use App\Models\IPFilter\IPFilter;
use App\Models\IPFilter\IPList;
use App\Support\AiChatModelCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class FirewallController extends Controller
{
    public function __construct(protected AiChatModelCatalog $modelCatalog) {}

    public function index(): View
    {
        $firewall = Firewall::query()->firstOrFail();

        return view('panel.firewall.index', [
            'ipFilters' => IPFilter::query()->get(),
            'firewall' => $firewall,
            'chatProviders' => $this->modelCatalog->getAvailableTextProviders(),
        ]);
    }

    public function save(FirewallSettingsRequest $request): RedirectResponse
    {
        $firewall = Firewall::query()->firstOrFail();
        $validated = $request->validated();

        if (! $request->boolean('ai_review_enabled')) {
            $validated['ai_enforcement_enabled'] = false;
            $validated['ai_provider'] = null;
            $validated['ai_model'] = null;
        }

        if (blank($validated['ai_provider'] ?? null)) {
            $validated['ai_provider'] = null;
            $validated['ai_model'] = null;
        }

        $providerCatalog = $this->modelCatalog->getAvailableTextProviders();

        if (! blank($validated['ai_provider'] ?? null) && ! array_key_exists($validated['ai_provider'], $providerCatalog)) {
            $validated['ai_provider'] = null;
            $validated['ai_model'] = null;
        }

        if (! blank($validated['ai_provider'] ?? null) && ! blank($validated['ai_model'] ?? null)) {
            $allowedModels = collect($providerCatalog[$validated['ai_provider']]['models'] ?? [])
                ->pluck('name')
                ->all();

            if (! in_array($validated['ai_model'], $allowedModels, true)) {
                $validated['ai_model'] = null;
            }
        } else {
            $validated['ai_model'] = null;
        }

        $firewall->update($validated);

        return redirect()->route('admin.firewall')->with('success', __('firewall.saved_success'));
    }

    public function logs(): View
    {
        return view('panel.firewall.logs');
    }

    /**
     * @throws \Exception
     */
    public function logsData(Request $request): JsonResponse
    {
        $query = FirewallLogs::with('ipFilter', 'ipList', 'ipList.filter');

        if ($request->has('order.0.name')) {
            $order = $request->input('order.0.name');
        } else {
            $order = 'created_at';
        }

        if ($request->has('order.0.dir')) {
            $dir = $request->input('order.0.dir');
        } else {
            $dir = 'desc';
        }

        $query->orderBy($order, $dir);

        return DataTables::eloquent($query)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->get('search')['value']) {
                    $search = $request->get('search')['value'];

                    $query->where('firewall_logs.ip', 'like', "%{$search}%")
                        ->orWhere('firewall_logs.url', 'like', "%{$search}%")
                        ->orWhere('firewall_logs.user_agent', 'like', "%{$search}%")
                        ->orWhere('firewall_logs.reason', 'like', "%{$search}%")
                        ->orWhere('firewall_logs.request_data', 'like', "%{$search}%")
                        ->orWhereHas('ipFilter', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                }
            })
            ->addColumn('ip', fn ($log) => $log->ip)
            ->addColumn('url', fn ($log) => $log->url)
            ->addColumn('user_agent', fn ($log) => $log->user_agent)
            ->addColumn('reason', fn ($log) => $log->reason)
            ->addColumn('request_data', fn ($log) => '<pre>'.htmlspecialchars(json_encode(json_decode($log->request_data), JSON_PRETTY_PRINT)).'</pre>')
            ->addColumn('ip_filter', fn ($log) => $log->ipFilter?->name)
            ->addColumn('created_at', fn ($log) => $log->created_at->format('d.m.Y H:i:s'))
            ->addColumn('actions', fn ($log) => view('panel.firewall.logs.actions', ['log' => $log]))
            ->rawColumns(['request_data', 'actions'])
            ->make(true);
    }

    public function whitelist(Request $request): JsonResponse
    {
        $firewall = Firewall::query()->firstOrFail();

        IpList::updateOrCreate(
            [
                'ip' => $request->ip,
            ],
            [
                'ip' => $request->ip,
                'filter_id' => $firewall->whitelist_rule_id,
            ]
        );

        return response()->json(['success' => true]);
    }

    public function delete(Request $request): JsonResponse
    {
        IpList::where('ip', $request->ip)->delete();

        return response()->json(['success' => true]);
    }
}
