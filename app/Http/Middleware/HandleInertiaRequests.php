<?php

namespace App\Http\Middleware;

use App\Support\ThemeData;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Define the props that are shared by default.
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'app' => [
                'name' => config('app.name'),
            ],
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->display_name ?: $user->full_name ?: $user->nickname,
                    'nickname' => $user->nickname,
                    'email' => $user->email,
                    'avatar' => replaceCDN($user->profile_image),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
            'site' => ThemeData::site(),
            'theme' => ThemeData::theme(),
            'languages' => ThemeData::languages(),
            'social' => ThemeData::social(),
            'navigation' => [
                'menu' => ThemeData::headerMenu(),
                'categories' => ThemeData::navigationCategories(),
            ],
            'translations' => ThemeData::translations(),
            'ads' => ThemeData::ads(),
            'analytics' => ThemeData::analytics(),
        ]);
    }
}
