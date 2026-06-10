<?php

use App\Actions\RouteRedirectAction;
use App\Http\Middleware\EarlyHintsMiddleware;
use App\Http\Middleware\FirewallMiddleware;
use App\Http\Middleware\GoogleAnalytics;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\Language;
use App\Http\Middleware\RouteRedirect;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Nwidart\Modules\Facades\Module;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: function () {
            // Module front routes must load BEFORE web.php so they match
            // before the catch-all /{showPost:slug} pattern.
            if (class_exists(Module::class)) {
                foreach (Module::allEnabled() as $module) {
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
                Language::class,
                HandleInertiaRequests::class,
                GoogleAnalytics::class,
                SecurityHeaders::class,
            ],
            prepend: [
                StartSession::class,
                EarlyHintsMiddleware::class,
                VerifyCsrfToken::class,
                FirewallMiddleware::class,
            ]);
        $middleware->use([
            TrustProxies::class,
            RouteRedirect::class,
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
