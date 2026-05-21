<?php

namespace App\Actions;

use App\Models\Post\PostIndexingLog;
use App\Models\Settings\GeneralSettings;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleIndexingAction
{
    private const INDEXING_SCOPE = 'https://www.googleapis.com/auth/indexing';

    private const INDEXING_ENDPOINT = 'https://indexing.googleapis.com/v3/urlNotifications:publish';

    private const INSPECTION_SCOPE = 'https://www.googleapis.com/auth/webmasters.readonly';

    private const INSPECTION_ENDPOINT = 'https://searchconsole.googleapis.com/v1/urlInspection/index:inspect';

    public function submit(string $url, string $type = 'URL_UPDATED'): array
    {
        $credentialsPath = storage_path('app/analytics/service-account-credentials.json');

        if (! file_exists($credentialsPath)) {
            Log::warning('Google Indexing: service-account-credentials.json not found.');

            return ['status' => 'failed', 'code' => 0, 'message' => 'Google indexing credentials not configured.'];
        }

        $credentials = json_decode(file_get_contents($credentialsPath), true);

        $token = $this->fetchAccessTokenForScope($credentials, self::INDEXING_SCOPE);

        if (! $token) {
            Log::error('Google Indexing: failed to obtain access token.', ['url' => $url]);

            return ['status' => 'failed', 'code' => 0, 'message' => 'Failed to obtain access token.'];
        }

        Log::info('Google Indexing API request', ['url' => $url, 'type' => $type]);

        $response = Http::withToken($token)
            ->post(self::INDEXING_ENDPOINT, [
                'url' => $url,
                'type' => $type,
            ]);

        Log::info('Google Indexing API response', [
            'url' => $url,
            'http_status' => $response->status(),
            'body' => $response->body(),
        ]);

        if ($response->successful()) {
            return ['status' => 'success', 'code' => $response->status(), 'message' => $response->body()];
        }

        return ['status' => 'failed', 'code' => $response->status(), 'message' => $response->body()];
    }

    public function inspect(string $url): array
    {
        $credentialsPath = storage_path('app/analytics/service-account-credentials.json');

        if (! file_exists($credentialsPath)) {
            return ['indexed' => false, 'coverage_state' => 'Credentials not configured', 'error' => true];
        }

        $credentials = json_decode(file_get_contents($credentialsPath), true);

        $token = $this->fetchAccessTokenForScope($credentials, self::INSPECTION_SCOPE);

        if (! $token) {
            Log::error('Google URL Inspection: failed to obtain access token.', ['url' => $url]);

            return ['indexed' => false, 'coverage_state' => 'Failed to get access token', 'error' => true];
        }

        $settings = GeneralSettings::first();
        $siteUrl = $settings?->google_indexing_site_url;

        if (! $siteUrl) {
            $parsed = parse_url($url);
            $siteUrl = ($parsed['scheme'] ?? 'https').'://'.($parsed['host'] ?? '').'/';
        }

        $response = Http::withToken($token)
            ->post(self::INSPECTION_ENDPOINT, [
                'inspectionUrl' => $url,
                'siteUrl' => $siteUrl,
            ]);

        Log::info('Google URL Inspection API response', [
            'url' => $url,
            'site_url' => $siteUrl,
            'http_status' => $response->status(),
            'body' => $response->body(),
        ]);

        if (! $response->successful()) {
            return [
                'indexed' => false,
                'coverage_state' => 'API error ('.$response->status().'): '.$response->body(),
                'error' => true,
            ];
        }

        $indexResult = $response->json('inspectionResult.indexStatusResult');

        if (! $indexResult) {
            return ['indexed' => false, 'coverage_state' => 'Unknown', 'verdict' => 'VERDICT_UNSPECIFIED', 'error' => false];
        }

        $indexed = ($indexResult['verdict'] ?? '') === 'PASS';

        return [
            'indexed' => $indexed,
            'coverage_state' => $indexResult['coverageState'] ?? 'Unknown',
            'verdict' => $indexResult['verdict'] ?? 'VERDICT_UNSPECIFIED',
            'last_crawl_time' => $indexResult['lastCrawlTime'] ?? null,
            'error' => false,
        ];
    }

    public function dailyQuotaReached(): bool
    {
        $limit = GeneralSettings::first()?->google_indexing_daily_limit ?? 200;
        $count = PostIndexingLog::where('status', 'success')->whereDate('created_at', today())->count();

        return $count >= $limit;
    }

    private function fetchAccessTokenForScope(array $credentials, string $scope): ?string
    {
        $serviceAccount = new ServiceAccountCredentials($scope, $credentials);
        $token = $serviceAccount->fetchAuthToken();

        return $token['access_token'] ?? null;
    }
}
