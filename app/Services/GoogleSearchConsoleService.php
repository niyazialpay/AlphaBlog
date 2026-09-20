<?php

namespace App\Services;

use App\Models\Settings\GeneralSettings;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleSearchConsoleService
{
    private const SCOPE = 'https://www.googleapis.com/auth/webmasters.readonly';

    private function getToken(): ?string
    {
        $credPath = storage_path('app/analytics/service-account-credentials.json');

        if (! file_exists($credPath)) {
            return null;
        }

        try {
            $credentials = json_decode(file_get_contents($credPath), true);
            $serviceAccount = new ServiceAccountCredentials(self::SCOPE, $credentials);
            $token = $serviceAccount->fetchAuthToken();

            return $token['access_token'] ?? null;
        } catch (\Throwable $e) {
            Log::warning('GSC: failed to get token', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * "Yapilandirilmamis" ile "istek basarisiz" ayrimi icin.
     *
     * Kimlik dosyasi `storage/app/analytics` altinda ve bu dizin gitignore'lu;
     * yani her kurulumda elle yerlestirilir. Site URL'si ise panelden girilen
     * bir ayar. Ikisinden biri eksikse ekran bunu ACIKCA soylemeli, "veri yok"
     * ya da genel bir "servis kullanilamiyor" mesaji vermemeli.
     */
    public function isConfigured(): bool
    {
        return file_exists(storage_path('app/analytics/service-account-credentials.json'))
            && $this->getSiteUrl() !== null;
    }

    private function getSiteUrl(): ?string
    {
        try {
            return GeneralSettings::first()?->google_indexing_site_url ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function query(string $siteUrl, string $token, array $body): array
    {
        $encodedUrl = rawurlencode($siteUrl);
        $endpoint = "https://searchconsole.googleapis.com/webmasters/v3/sites/{$encodedUrl}/searchAnalytics/query";
        $response = Http::withToken($token)->post($endpoint, $body);

        if (! $response->successful()) {
            Log::warning('GSC API error', ['status' => $response->status(), 'body' => $response->body()]);

            return [];
        }

        $rows = $response->json('rows', []);

        if ($rows === []) {
            /*
             * 200 + sifir satir SESSIZ bir bosluktu ve arayuzde "veri yok"tan
             * ayirt edilemiyordu. En sik sebebi mulk bicimi: ayni servis hesabi
             * `sc-domain:ornek.com` icin veri dondururken `https://ornek.com/`
             * icin bos donebilir. Hangi site adresiyle sorulduğunu loga yaz ki
             * neden bos oldugu anlasilabilsin.
             */
            Log::info('GSC: sorgu 200 dondu ama satir yok', [
                'site' => $siteUrl,
                'dimensions' => $body['dimensions'] ?? [],
                'startDate' => $body['startDate'] ?? null,
                'endDate' => $body['endDate'] ?? null,
            ]);
        }

        return $rows;
    }

    public function getPerformance(Carbon $startDate, Carbon $endDate): array
    {
        $token = $this->getToken();

        if (! $token) {
            return ['current' => [], 'previous' => []];
        }

        $siteUrl = $this->getSiteUrl();

        if (! $siteUrl) {
            return ['current' => [], 'previous' => []];
        }

        $cacheKey = config('cache.prefix').'gsc_perf_'.$siteUrl.'_'.$startDate->format('Y-m-d').'_'.$endDate->format('Y-m-d');

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($token, $siteUrl, $startDate, $endDate): array {
            $currentRows = $this->query($siteUrl, $token, [
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
            ]);

            $current = [];
            if (! empty($currentRows[0])) {
                $row = $currentRows[0];
                $current = [
                    'clicks' => (int) $row['clicks'],
                    'impressions' => (int) $row['impressions'],
                    'ctr' => round((float) $row['ctr'] * 100, 2),
                    'position' => round((float) $row['position'], 1),
                ];
            }

            $diffDays = $startDate->diffInDays($endDate);
            $prevEnd = $startDate->copy()->subDay();
            $prevStart = $prevEnd->copy()->subDays($diffDays);

            $previousRows = $this->query($siteUrl, $token, [
                'startDate' => $prevStart->format('Y-m-d'),
                'endDate' => $prevEnd->format('Y-m-d'),
            ]);

            $previous = [];
            if (! empty($previousRows[0])) {
                $row = $previousRows[0];
                $previous = [
                    'clicks' => (int) $row['clicks'],
                    'impressions' => (int) $row['impressions'],
                    'ctr' => round((float) $row['ctr'] * 100, 2),
                    'position' => round((float) $row['position'], 1),
                ];
            }

            return ['current' => $current, 'previous' => $previous];
        });
    }

    public function getKeywords(Carbon $startDate, Carbon $endDate, int $limit = 50): array
    {
        $token = $this->getToken();

        if (! $token) {
            return [];
        }

        $siteUrl = $this->getSiteUrl();

        if (! $siteUrl) {
            return [];
        }

        $cacheKey = config('cache.prefix').'gsc_keywords_'.$siteUrl.'_'.$startDate->format('Y-m-d').'_'.$endDate->format('Y-m-d');

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($token, $siteUrl, $startDate, $endDate, $limit): array {
            $rows = $this->query($siteUrl, $token, [
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
                'dimensions' => ['query'],
                'rowLimit' => $limit,
                'orderBy' => [['fieldName' => 'clicks', 'sortOrder' => 'DESCENDING']],
            ]);

            return array_map(fn (array $row): array => [
                'query' => $row['keys'][0],
                'clicks' => (int) $row['clicks'],
                'impressions' => (int) $row['impressions'],
                'ctr' => round((float) $row['ctr'] * 100, 2),
                'position' => round((float) $row['position'], 1),
            ], $rows);
        });
    }

    public function getClicksTrend(Carbon $startDate, Carbon $endDate): array
    {
        $token = $this->getToken();

        if (! $token) {
            return [];
        }

        $siteUrl = $this->getSiteUrl();

        if (! $siteUrl) {
            return [];
        }

        $cacheKey = config('cache.prefix').'gsc_trend_'.$siteUrl.'_'.$startDate->format('Y-m-d').'_'.$endDate->format('Y-m-d');

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($token, $siteUrl, $startDate, $endDate): array {
            $rows = $this->query($siteUrl, $token, [
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
                'dimensions' => ['date'],
                'orderBy' => [['fieldName' => 'date', 'sortOrder' => 'ASCENDING']],
            ]);

            return array_map(fn (array $row): array => [
                'date' => $row['keys'][0],
                'clicks' => (int) $row['clicks'],
                'impressions' => (int) $row['impressions'],
            ], $rows);
        });
    }
}
