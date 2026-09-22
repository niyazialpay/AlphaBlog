<?php

use App\Actions\RouteRedirectAction;
use App\Http\Middleware\EarlyHintsMiddleware;
use App\Http\Middleware\FirewallMiddleware;
use App\Http\Middleware\GoogleAnalytics;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\HandlePanelInertiaRequests;
use App\Http\Middleware\Language;
use App\Http\Middleware\RouteRedirect;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\VerifyCsrfToken;
use App\Support\Panel\Panel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: function () {
            if (class_exists(Module::class)) {
                $panelPath = preg_quote((string) config('settings.admin_panel_path', 'admin'), '#');

                foreach (Module::allEnabled() as $module) {
                    $frontPath = $module->getPath().'/routes/front.php';
                    if (is_file($frontPath)) {
                        Route::domain(config('app.url'))
                            ->middleware('web')
                            ->prefix('/{language}')
                            ->where(['language' => '(?!'.$panelPath.'(?:/|$))[^/]+'])
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
                HandlePanelInertiaRequests::class,
                GoogleAnalytics::class,
                SecurityHeaders::class,
            ],
            prepend: [
                EarlyHintsMiddleware::class,
                FirewallMiddleware::class,
            ],
            replace: [
                PreventRequestForgery::class => VerifyCsrfToken::class,
            ],
        );
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

        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            if (! $request->inertia()) {
                return $response;
            }

            $status = $response->getStatusCode();

            if ($status === 419) {
                return back()->with('error', __('general.page_expired'));
            }

            if (! Panel::isPanelRequest($request)) {
                return $response;
            }

            if (! in_array($status, [403, 404, 500, 503], true)) {
                return $response;
            }

            return Inertia::render('Errors/Error', ['status' => $status])
                ->toResponse($request)
                ->setStatusCode($status);
        });
    })->create();
