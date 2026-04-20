<?php

use App\Actions\RouteRedirectAction;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: function () {
            // Module front routes must load BEFORE web.php so they match
            // before the catch-all /{showPost:slug} pattern.
            if (class_exists(\Nwidart\Modules\Facades\Module::class)) {
                foreach (\Nwidart\Modules\Facades\Module::allEnabled() as $module) {
                    $frontPath = $module->getPath().'/routes/front.php';
                    if (is_file($frontPath)) {
                        Route::domain(config('app.url'))
                            ->middleware('web')
                            ->prefix('/{language}')
                            ->group($frontPath);
                    }
                }
            }

            Route::middleware('web')->group(base_path('routes/web.php'));
            Route::middleware('api')->prefix('api')->group(base_path('routes/api.php'));
        },
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(
            append: [
                \App\Http\Middleware\Language::class,
                \App\Http\Middleware\HandleInertiaRequests::class,
            ],
            prepend: [
                \Illuminate\Session\Middleware\StartSession::class,
                \App\Http\Middleware\EarlyHintsMiddleware::class,
                \App\Http\Middleware\VerifyCsrfToken::class,
                \App\Http\Middleware\FirewallMiddleware::class,
            ]);
        $middleware->use([
            \App\Http\Middleware\TrustProxies::class,
            \App\Http\Middleware\RouteRedirect::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Request $request) {
            $route = RouteRedirectAction::RouteRedirect($request);
            if ($route) {
                if ($route->redirect_code == 404) {
                    abort(404);
                } else {
                    return redirect($route->new_url, (int) $route->redirect_code);
                }
            }

            return $request;
        });
    })->create();
