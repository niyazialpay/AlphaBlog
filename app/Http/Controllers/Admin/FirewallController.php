<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Firewall\FirewallLogs;
use App\Models\IPFilter\IPList;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Exceptions\Exception;

class FirewallController extends Controller
{
    public function index()
    {
        return view('panel.firewall.index', [
            'ipFilters' => \App\Models\IPFilter\IPFilter::all(),
            'firewall' => \App\Models\Firewall\Firewall::first(),
        ]);
    }

    public function save(Request $request)
    {
        $firewall = \App\Models\Firewall\Firewall::first();
        $firewall->update($request->except('_token'));

        return redirect()->route('admin.firewall')->with('success', __('firewall.saved_success'));
    }

    public function logs()
    {
        return view('panel.firewall.logs');
    }

    /**
     * @throws \Exception
     */
    public function logsData(Request $request)
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
            ->addColumn('ip', fn($log) => $log->ip)
            ->addColumn('url', fn($log) => $log->url)
            ->addColumn('user_agent', fn($log) => $log->user_agent)
            ->addColumn('reason', fn($log) => $log->reason)
            ->addColumn('request_data', fn($log) => '<pre>' . htmlspecialchars(json_encode(json_decode($log->request_data), JSON_PRETTY_PRINT)) . '</pre>')
            ->addColumn('ip_filter', fn($log) => $log->ipFilter?->name)
            ->addColumn('created_at', fn($log) => $log->created_at->format('Y-m-d H:i:s'))
            ->addColumn('actions', fn($log) => view('panel.firewall.logs.actions', ['log' => $log]))
            ->rawColumns(['request_data', 'actions'])
            ->make(true);
    }

    public function whitelist(Request $request)
    {
        $firewall = \App\Models\Firewall\Firewall::first();
        IpList::updateOrCreate(
            [
                'ip'         => $request->ip,
            ],
            [
                'ip'         => $request->ip,
                'filter_id' => $firewall->whitelist_rule_id,
            ]
        );

        return response()->json(['success' => true]);
    }

    public function delete(Request $request)
    {
        IpList::where('ip', $request->ip)->delete();

        return response()->json(['success' => true]);
    }
}
