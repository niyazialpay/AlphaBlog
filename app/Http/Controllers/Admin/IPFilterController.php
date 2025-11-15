<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\IPFilter\IPFilterRequest;
use App\Http\Requests\IPFilter\ToogleRequest;
use App\Models\IPFilter\IPFilter;
use App\Models\IPFilter\IPList;
use App\Models\IPFilter\RouteList;
use App\Support\IPFilterCache;
use App\Support\TrustedBots;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IPFilterController extends Controller
{
    public function index()
    {
        $IPFilter = IPFilter::class;
        $IPFilter = $IPFilter::with('ipList', 'routeList')->get();

        return view('panel.ip_filter.index', compact('IPFilter'));
    }

    public function show(IPFilter $ip_filter)
    {
        return view('panel.ip_filter.show', [
            'ip_filter' => $ip_filter,
            'route_list' => \Illuminate\Support\Facades\Route::getRoutes(),
        ]);
    }

    public function save(IPFilter $ip_filter, IPFilterRequest $request)
    {
        try {
            DB::beginTransaction();
            if (is_array(request()->post('routes'))) {
                $routes = $request->post('routes');
            } else {
                $routes = [];
                foreach (explode(PHP_EOL, $request->post('routes')) as $item) {
                    $item = trim($item);
                    if ($item) {
                        $routes[] = $item;
                    }
                }
            }
            $ip_range = [];
            foreach (explode(PHP_EOL, $request->post('ip_range')) as $item) {
                $item = trim($item);
                if ($item) {
                    $ip_range[] = $item;
                }
            }
            [$ip_range, $trustedSkipped] = TrustedBots::filterOutTrusted($ip_range);
            if ($ip_filter->id) {
                $message = __('ip_filter.success_update');
            } else {
                $message = __('ip_filter.success');
            }
            $ip_filter->name = $request->post('name');
            $ip_filter->list_type = $request->post('list_type');
            $ip_filter->is_active = $request->post('is_active') == 1;
            $ip_filter->route_type = $request->post('route_type');
            $ip_filter->code = $request->post('code');
            $ip_filter->save();
            $ip_filter->ipList()->delete();
            foreach ($ip_range as $item) {
                $ip_list = new IPList;
                $ip_list->ip = $item;
                $ip_filter->ipList()->save($ip_list);
            }

            $ip_filter->routeList()->delete();
            foreach ($routes as $item) {
                $route_list = new RouteList;
                $route_list->route = $item;
                $ip_filter->routeList()->save($route_list);
            }
            $this->cacheRefresh();
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => $message,
                'trusted_skipped' => $trustedSkipped,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function delete(Request $request)
    {
        try {
            DB::beginTransaction();
            $IPFilter = IPFilter::class;
            $IPFilter::where('id', $request->post('id'))->delete();
            $this->cacheRefresh();
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => __('ip_filter.success_delete'),
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function toggleStatus(ToogleRequest $request)
    {
        try {
            DB::beginTransaction();
            $ip = IPFilter::where('id', $request->post('id'))->first();
            $ip->is_active = ! $ip->is_active;
            $ip->save();
            $this->cacheRefresh();
            DB::commit();

            return response()->json([
                'status' => true,
                'rule' => $ip->is_active,
                'message' => __('ip_filter.success_update'),
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function bulkStoreIps(IPFilter $ip_filter, Request $request)
    {
        $request->validate([
            'ips' => 'required|string',
        ]);

        $rawIps = collect(preg_split('/[\r\n,;]+/', (string) $request->post('ips'), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($ip) => trim((string) $ip))
            ->filter(fn ($ip) => $ip !== '');

        if ($rawIps->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => __('ip_filter.ip_range_required'),
            ]);
        }

        $validIps = collect();
        $invalidIps = collect();

        foreach ($rawIps->unique() as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                $validIps->push($ip);
            } else {
                $invalidIps->push($ip);
            }
        }

        if ($validIps->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => __('ip_filter.no_valid_ip') ?? 'No valid IP address detected.',
                'invalid' => $invalidIps->values(),
            ]);
        }

        [$filteredIps, $trustedSkipped] = TrustedBots::filterOutTrusted($validIps->all());
        $validIps = collect($filteredIps);

        if ($validIps->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => __('ip_filter.trusted_ip_not_allowed') ?? 'Trusted bot IPs cannot be filtered.',
                'trusted_skipped' => $trustedSkipped,
            ]);
        }

        DB::beginTransaction();

        try {
            $existing = $ip_filter->ipList()
                ->whereIn('ip', $validIps->all())
                ->pluck('ip');

            $uniqueToInsert = $validIps->diff($existing);

            $created = collect();

            if ($uniqueToInsert->isNotEmpty()) {
                $payload = $uniqueToInsert->map(fn ($ip) => ['ip' => $ip])->all();
                $created = collect($ip_filter->ipList()->createMany($payload));
            }

            $this->cacheRefresh();
            DB::commit();

            return response()->json([
                'status' => true,
                'added' => $created->map(fn (IPList $model) => [
                    'id' => $model->id,
                    'ip' => $model->ip,
                ]),
                'duplicates' => $validIps->intersect($existing)->values(),
                'invalid' => $invalidIps->values(),
                'trusted_skipped' => $trustedSkipped,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function destroyIp(IPFilter $ip_filter, IPList $ip_list)
    {
        if ($ip_list->filter_id !== $ip_filter->id) {
            abort(404);
        }

        DB::beginTransaction();

        try {
            $ip_list->delete();
            $this->cacheRefresh();
            DB::commit();

            return response()->json([
                'status' => true,
                'id' => $ip_list->id,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function cacheRefresh()
    {
        IPFilterCache::refresh();
    }
}
