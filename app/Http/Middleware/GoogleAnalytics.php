<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class GoogleAnalytics
{
    private const COOKIE_NAME = '_ga_cid';

    private const COOKIE_DAYS = 730; // 2 years

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldTrack($request, $response)) {
            $clientId = $this->resolveClientId($request);

            // Cookie yalnızca front-end (app.blade / e-dergi reader) onu üretmediyse
            // burada yazılır; aksi halde mükerrer cookie set edilmez.
            if (! app()->bound('ga_client_id') && ! $request->cookie(self::COOKIE_NAME)) {
                $response->cookie(self::COOKIE_NAME, $clientId, self::COOKIE_DAYS * 60 * 24);
            }

            $payload = $this->buildPayload($request, $clientId);

            // Defer until after response is sent to client — zero latency impact
            defer(fn () => $this->send($payload));
        }

        return $response;
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        if (! $this->measurementId()) {
            return false;
        }

        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return false;
        }

        if ($request->is(config('app.admin_panel_path', 'admin').'/*', config('app.admin_panel_path', 'admin'))) {
            return false;
        }

        if (! in_array($response->getStatusCode(), [200, 301, 302])) {
            return false;
        }

        // Skip common bots
        $ua = strtolower($request->userAgent() ?? '');
        foreach (['bot', 'crawler', 'spider', 'slurp', 'facebookexternalhit', 'lighthouse', 'pagespeed'] as $bot) {
            if (str_contains($ua, $bot)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Tek client_id otoritesi: front-end (app.blade / reader) ürettiği değer
     * app()->instance('ga_client_id', ...) ile paylaşılır; böylece gtag.js ile
     * server-side Measurement Protocol AYNI client_id'yi kullanır (hybrid eşleşme).
     */
    private function resolveClientId(Request $request): string
    {
        if (app()->bound('ga_client_id')) {
            return (string) app('ga_client_id');
        }

        return $request->cookie(self::COOKIE_NAME) ?? Str::uuid()->toString();
    }

    private function buildPayload(Request $request, string $clientId): array
    {
        $params = [
            'page_location' => $request->fullUrl(),
            'page_referrer' => $request->headers->get('referer', ''),
            'language' => $request->getPreferredLanguage() ?? 'tr',
        ];

        foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'] as $utm) {
            if ($request->filled($utm)) {
                $params[$utm] = $request->query($utm);
            }
        }

        return [
            'client_id' => $clientId,
            'user_agent' => $request->userAgent(),
            'ip_override' => $request->ip(),
            'non_personalized_ads' => false,
            'events' => [[
                'name' => 'page_view',
                'params' => $params,
            ]],
        ];
    }

    private function send(array $payload): void
    {
        $measurementId = $this->measurementId();
        $apiSecret = $this->apiSecret();

        if (! $measurementId || ! $apiSecret) {
            return;
        }

        try {
            Http::timeout(5)->post(
                "https://www.google-analytics.com/mp/collect?measurement_id={$measurementId}&api_secret={$apiSecret}",
                $payload
            );
        } catch (\Throwable) {
            // Silently fail — analytics should never break the app
        }
    }

    /**
     * Önce panelden yönetilen ayar (analytics_settings), yoksa .env fallback.
     */
    private function measurementId(): ?string
    {
        $fromDb = app()->bound('analytic_settings')
            ? optional(app('analytic_settings'))->ga_measurement_id
            : null;

        return $fromDb ?: config('services.google_analytics.measurement_id');
    }

    private function apiSecret(): ?string
    {
        $fromDb = app()->bound('analytic_settings')
            ? optional(app('analytic_settings'))->ga_api_secret
            : null;

        return $fromDb ?: config('services.google_analytics.api_secret');
    }
}
