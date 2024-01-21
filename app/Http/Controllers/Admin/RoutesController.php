<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RouteRequest;
use App\Models\Routes;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoutesController extends Controller
{
    function index(): Application|Factory|View
    {
        return view('panel/routes', ['routes' => Routes::paginate(10)]);
    }

    function show(Routes $route): JsonResponse
    {
        return response()->json($route);
    }

    function delete(Request $request){
        $route = Routes::find($request->post('route_id'));
        $route->delete();
        return response()->json(['success' => true]);
    }

    public function save(Routes $route, RouteRequest $request){
        $route->old_url = $request->post('old_url');
        $route->new_url = $request->post('new_url');
        $route->redirect_code = $request->post('redirect_code');
        $route->save();
        return response()->json(['success' => true]);
    }

}
