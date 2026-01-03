<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RouteRequest;
use App\Models\RouteRedirects;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class RouteRedirectsController extends Controller
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */

    public function index(): Application|Factory|View
    {
        $routes = RouteRedirects::where(function($query){
            if(request()->has('search') && request()->get('search') != null){
                $query->where('old_url', 'LIKE', '%'.request()->get('search').'%');
                $query->orWhere('new_url', 'LIKE', '%'.request()->get('search').'%');
            }
        })->orderBy('created_at', 'DESC')->paginate(10);
        return view('panel/redirects', ['routes' => $routes]);
    }

    public function show(RouteRedirects $route): JsonResponse
    {
        return response()->json($route);
    }

    public function delete(Request $request)
    {
        try {
            DB::beginTransaction();
            $route = RouteRedirects::find($request->post('route_id'));
            Cache::forget(config('cache.prefix').'routes_'.Str::slug($route->old_url));
            $route->delete();
            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false]);
        }
    }

    public function save(RouteRedirects $route, RouteRequest $request)
    {
        try {
            DB::beginTransaction();
            $route->old_url = $request->post('old_url');
            $route->new_url = $request->post('new_url');
            $route->redirect_code = $request->post('redirect_code');
            $route->save();
            Cache::forget(config('cache.prefix').'routes_'.Str::slug($route->old_url));
            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false]);
        }
    }
}
