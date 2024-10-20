<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\IPFilter\IPFilterRequest;
use App\Http\Requests\IPFilter\ToogleRequest;
use App\Models\IPFilter\IPFilter;
use App\Models\IPFilter\IPList;
use App\Models\IPFilter\RouteList;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

    private function cacheRefresh()
    {
        Cache::forget(config('cache.prefix').'ip_filter');
        Cache::rememberForever(config('cache.prefix').'ip_filter', function () {
            return IPFilter::with('ipList', 'routeList')->where('is_active', true)->get();
        });
    }
}
