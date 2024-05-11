<?php

use App\Action\RouteRedirectAction;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
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
            //\App\Http\Middleware\VerifyCsrfToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            $route = RouteRedirectAction::RouteRedirect($request);
            if ($route) {
                if ($route->redirect_code == 404) {
                    abort(404);
                } else {
                    return redirect($route->new_url, (int) $route->redirect_code);
                }
            }
            if ($e->getStatusCode() == 404) {
                try{
                    return response()->view('themes.'.app('theme')->name.'.404', [], 404);
                }
                catch (Exception $e){
                    return response()->view('Default.404', [], 404);
                }
            }

            return $request;
        });
    })->create();
