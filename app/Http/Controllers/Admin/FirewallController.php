<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FirewallSettingsRequest;
use App\Models\Firewall\Firewall;
use App\Models\Firewall\FirewallLogs;
use App\Models\IPFilter\IPFilter;
use App\Models\IPFilter\IPList;
use App\Support\AiChatModelCatalog;
use App\Support\Panel\PanelResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Yajra\DataTables\Facades\DataTables;

class FirewallController extends Controller
{
    public function __construct(protected AiChatModelCatalog $modelCatalog) {}

    public function index(): SymfonyResponse
    {
        $firewall = Firewall::query()->firstOrFail();
        $filters = IPFilter::query()->get();

        return PanelResponse::render(
            'Firewall/Index',
            'panel.firewall.index',
            [
                'firewall' => $firewall->only($firewall->getFillable()),
                'ipFilters' => $filters->map(fn (IPFilter $filter) => [
                    'id' => $filter->id,
                    'name' => $filter->name,
                    'list_type' => $filter->list_type,
                ])->values(),
                'chatProviders' => $this->modelCatalog->getAvailableTextProviders(),
            ],
            [
                'ipFilters' => $filters,
                'firewall' => $firewall,
                'chatProviders' => $this->modelCatalog->getAvailableTextProviders(),
            ],
        );
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

    public function logs(Request $request): SymfonyResponse
    {
        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 75, 100], true) ? $perPage : 10;
        $search = trim((string) $request->get('search'));

        $logs = FirewallLogs::with('ipFilter', 'ipList')
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search) {
                foreach (['ip', 'url', 'user_agent', 'reason'] as $column) {
                    $q->orWhere($column, 'like', '%'.$search.'%');
                }
            }))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return PanelResponse::render(
            'Firewall/Logs',
            'panel.firewall.logs',
            [
                'logs' => PanelResponse::rows($logs, fn ($log) => [
                    'id' => $log->id,
                    'ip' => $log->ip,
                    'url' => $log->url,
                    'user_agent' => $log->user_agent,
                    'reason' => $log->reason,
                    // Gercek nesne; eski uc '<pre>'.htmlspecialchars(...) basiyordu.
                    'request_data' => json_decode((string) $log->request_data, true),
                    'ip_filter' => $log->ipFilter ? ['id' => $log->ipFilter->id, 'name' => $log->ipFilter->name] : null,
                    // Satir aksiyonlarini surer: blade partial'i bu kosulu isliyordu.
                    'is_blacklisted' => $log->ipList && $log->ipList->ip === $log->ip
                        && $log->ipFilter?->list_type === 'blacklist',
                    'createdAt' => $log->created_at?->toIso8601String(),
                ]),
                'filters' => ['search' => $search !== '' ? $search : null, 'per_page' => $perPage],
            ],
            [],
        );
    }

    /**
     * @throws \Exception
     */
    public function logsData(Request $request): JsonResponse
    {
        $query = FirewallLogs::with('ipFilter', 'ipList', 'ipList.filter');

        $order = (string) $request->input('order.0.name', 'created_at');
        if (! Schema::hasColumn((new FirewallLogs)->getTable(), $order)) {
            $order = 'created_at';
        }
        $dir = strtolower((string) $request->input('order.0.dir')) === 'asc' ? 'asc' : 'desc';

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

    public function whitelist(Request $request): SymfonyResponse
    {
        $firewall = Firewall::query()->firstOrFail();

        IPList::updateOrCreate(
            [
                'ip' => $request->ip,
            ],
            [
                'ip' => $request->ip,
                'filter_id' => $firewall->whitelist_rule_id,
            ]
        );

        return $request->inertia()
            ? back()->with('success', __('firewall.added_to_whitelist'))
            : response()->json(['success' => true]);
    }

    public function delete(Request $request): SymfonyResponse
    {
        IPList::where('ip', $request->ip)->delete();

        return $request->inertia()
            ? back()->with('success', __('general.deleted'))
            : response()->json(['success' => true]);
    }
}
