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
            // Module front routes must load BEFORE web.php so they match
            // before the catch-all /{showPost:slug} pattern.
            //
            // BUG: bu oncelik yonetim panelini de golgeliyordu. `{language}`
            // kisitsiz oldugu icin /admin/about, BirderAkademi'nin
            // /{language}/{about} route'una (language=admin, about=about)
            // dusuyor ve panelin Hakkinda ekrani yerine sitenin Hakkinda
            // sayfasi render ediliyordu. Panel path'i artik dil olarak
            // eslesemez; modul kodu degismiyor.
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
                // Panel yuzeyi kendi root view'u ve prop sozlesmesiyle devrali;
                // panel disi isteklerde hemen cekilir. Sira onemli: setRootView sonuncu kazanir.
                HandlePanelInertiaRequests::class,
                GoogleAnalytics::class,
                SecurityHeaders::class,
            ],
            prepend: [
                EarlyHintsMiddleware::class,
                FirewallMiddleware::class,
            ],
            /*
             * StartSession ve VerifyCsrfToken ARTIK PREPEND EDILMIYOR.
             *
             * `prependToGroup` sonucu `array_unique(array_merge($prepends, $group))`
             * ile birlestiriyor: StartSession her iki listede de bulundugu icin
             * array_unique ILK gecisi (prepend edilmis olani) tutuyor, gruptaki
             * asil sirayi DUSURUYORDU. Yani oturum `EncryptCookies`'ten once
             * basliyordu; oturum ve XSRF-TOKEN cerezleri sifrelenmeden gidip
             * geliyor, sunucu ise `X-XSRF-TOKEN` basligini decrypt etmeye
             * calistigi icin cerez tabanli CSRF yolu tamamen oluyordu.
             *
             * `replace` ile uygulamanin muaf yollarini tasiyan alt sinif
             * framework middleware'inin YERINE konur; sira kanonik kalir:
             * EncryptCookies -> AddQueuedCookies -> StartSession ->
             * ShareErrorsFromSession -> VerifyCsrfToken -> SubstituteBindings.
             */
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

            /*
             * Oturum/CSRF suresi doldu.
             *
             * Panel kontrolunden ONCE ve ondan BAGIMSIZ: Inertia istemcisi HTML
             * bir hata sayfasini render EDEMEZ, tam ekran bir modal acar ve
             * kullanici bembeyaz bir kutu gorur. `Panel::isPanelRequest()` yol
             * tabanli; ADMIN_PANEL_PATH beklenmedik bir degerse ya da istek
             * panel onekinin disindan geliyorsa false donerdi ve 419 yine modal
             * olurdu. X-Inertia tasiyan her istek icin geri yonlendirme dogru
             * yanittir; istemci flash'i okuyup toast basar.
             */
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
