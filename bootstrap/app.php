<?php

use App\Actions\RouteRedirectAction;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\TrustProxies::class,
            \App\Http\Middleware\Language::class,
            \App\Http\Middleware\IpFilter::class,
            \App\Http\Middleware\RouteRedirect::class,
            \App\Http\Middleware\DisableCookiesForCdn::class,
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
