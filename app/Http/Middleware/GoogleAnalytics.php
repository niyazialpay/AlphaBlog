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

            if (! $request->cookie(self::COOKIE_NAME)) {
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
        if (! config('services.google_analytics.measurement_id')) {
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

    private function resolveClientId(Request $request): string
    {
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
        $measurementId = config('services.google_analytics.measurement_id');
        $apiSecret = config('services.google_analytics.api_secret');

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
}
