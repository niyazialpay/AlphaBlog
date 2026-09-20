<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RouteRequest;
use App\Models\RouteRedirects;
use App\Support\Panel\PanelResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

class RouteRedirectsController extends Controller
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function index(): Response
    {
        $routes = RouteRedirects::where(function ($query) {
            if (request()->has('search') && request()->get('search') != null) {
                $query->where('old_url', 'LIKE', '%'.request()->get('search').'%');
                $query->orWhere('new_url', 'LIKE', '%'.request()->get('search').'%');
            }
        })->orderBy('created_at', 'DESC')->paginate(10)->withQueryString();

        return PanelResponse::render(
            'Redirects/Index',
            'panel/redirects',
            [
                'routes' => PanelResponse::rows($routes, fn (RouteRedirects $route) => [
                    'id' => $route->id,
                    'old_url' => $route->old_url,
                    'new_url' => $route->new_url,
                    'redirect_code' => (int) $route->redirect_code,
                    'createdAt' => $route->created_at?->toIso8601String(),
                ]),
                'filters' => ['search' => request()->get('search')],
            ],
            ['routes' => $routes],
        );
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

            // R2: Inertia yonlendirme alir; jQuery cagiranlar ayni JSON'u almaya devam eder.
            return $request->inertia()
                ? back()->with('success', __('general.deleted'))
                : response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();

            return $request->inertia()
                ? back()->with('error', __('general.error'))
                : response()->json(['success' => false]);
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

            return $request->inertia()
                ? back()->with('success', __('general.saved'))
                : response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();

            return $request->inertia()
                ? back()->with('error', __('general.error'))
                : response()->json(['success' => false]);
        }
    }
}
