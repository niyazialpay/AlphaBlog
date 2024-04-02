<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\IPFilter\IPFilterRequest;
use App\Models\IPFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class IPFilterController extends Controller
{
    public function index()
    {
        $IPFilter = \App\Models\IPFilter::class;
        $IPFilter = $IPFilter::all();

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
        $ip_filter->ip_range = $ip_range;
        $ip_filter->routes = $routes;
        $ip_filter->list_type = $request->post('list_type');
        $ip_filter->is_active = $request->post('is_active') == 1;
        $ip_filter->route_type = $request->post('route_type');
        $ip_filter->save();

        $this->cacheRefresh();

        return response()->json([
            'status' => true,
            'message' => $message,
        ]);
    }

    public function delete(Request $request)
    {
        $IPFilter = \App\Models\IPFilter::class;
        $IPFilter::where('_id', $request->post('id'))->delete();
        $this->cacheRefresh();

        return response()->json([
            'status' => true,
            'message' => __('ip_filter.success_delete'),
        ]);
    }

    private function cacheRefresh()
    {
        Cache::forget(config('cache.prefix').'ip_filter');
        Cache::rememberForever(config('cache.prefix').'ip_filter', function () {
            return \App\Models\IPFilter::where('is_active', true)->get();
        });
    }
}
