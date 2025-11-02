<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EarlyHintsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->frankenphp_send_early_hints([
            '<https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css>; rel=preload; as=style',
            '<https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css>; rel=preload; as=style',
            '<'.asset('themes/fontawesome/css/all.min.css').'>; rel=preload; as=style',
            '<'.asset('theme/Cryptograph/css/animate.min.css').'>; rel=preload; as=style',
            '<'.asset('theme/Cryptograph/css/bootstrap.min.css').'>; rel=preload; as=style',
            '<'.asset('theme/Cryptograph/css/slick.min.css').'>; rel=preload; as=style',
            '<'.asset('theme/Cryptograph/css/default.min.css').'>; rel=preload; as=style',
            '<'.asset('theme/Cryptograph/css/style.min.css').'>; rel=preload; as=style',
            '<'.asset('theme/Cryptograph/css/responsive.min.css').'>; rel=preload; as=style',
            '<'.asset('theme/Cryptograph/css/custom.min.css').'>; rel=preload; as=style',
            '<'.asset('theme/Cryptograph/js/vendor/jquery-3.6.0.min.js').'>; rel=preload; as=script',
            '<'.asset('theme/Cryptograph/js/bootstrap.min.js').'>; rel=preload; as=script',
            '<'.asset('theme/Cryptograph/js/main.min.js').'>; rel=preload; as=script',
            '<https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css>; rel=preload; as=style',
            '<https://cdn.jsdelivr.net/npm/sweetalert2@11>; rel=preload; as=script',
            '<'.asset('themes/fontawesome/webfonts/fa-solid-900.woff2').'>; rel=preload; as=font; type=font/woff2; crossorigin',
            '<'.asset('themes/fontawesome/webfonts/fa-brands-400.woff2').'>; rel=preload; as=font; type=font/woff2; crossorigin',
            '<'.asset('themes/fontawesome/webfonts/fa-duotone-900.woff2').'>; rel=preload; as=font; type=font/woff2; crossorigin',
            '<'.asset('themes/fontawesome/webfonts/fa-regular-400.woff2').'>; rel=preload; as=font; type=font/woff2; crossorigin',
        ]);

        return $next($request);
    }

    private function frankenphp_send_early_hints(array $links): void
    {
        foreach ($links as $link) {
            header('Link: '.$link);
            if(function_exists('headers_send')){
                headers_send(103);
            }
        }
    }
}
