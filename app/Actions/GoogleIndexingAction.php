<?php

namespace App\Actions;

use App\Models\Post\PostIndexingLog;
use App\Models\Settings\GeneralSettings;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;

class GoogleIndexingAction
{
    private const INDEXING_SCOPE = 'https://www.googleapis.com/auth/indexing';

    private const INDEXING_ENDPOINT = 'https://indexing.googleapis.com/v3/urlNotifications:publish';

    public function submit(string $url, string $type = 'URL_UPDATED'): array
    {
        $settings = GeneralSettings::first();
        $credentialsJson = $settings?->google_indexing_credentials;

        if (! $credentialsJson) {
            return ['status' => 'failed', 'code' => 0, 'message' => 'Google indexing credentials not configured.'];
        }

        $credentials = json_decode($credentialsJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['status' => 'failed', 'code' => 0, 'message' => 'Invalid credentials JSON.'];
        }

        $token = $this->fetchAccessToken($credentials);

        if (! $token) {
            return ['status' => 'failed', 'code' => 0, 'message' => 'Failed to obtain access token.'];
        }

        $response = Http::withToken($token)
            ->post(self::INDEXING_ENDPOINT, [
                'url' => $url,
                'type' => $type,
            ]);

        if ($response->successful()) {
            return ['status' => 'success', 'code' => $response->status(), 'message' => $response->body()];
        }

        return ['status' => 'failed', 'code' => $response->status(), 'message' => $response->body()];
    }

    public function dailyQuotaReached(): bool
    {
        $limit = GeneralSettings::first()?->google_indexing_daily_limit ?? 200;
        $count = PostIndexingLog::where('status', 'success')->whereDate('created_at', today())->count();

        return $count >= $limit;
    }

    private function fetchAccessToken(array $credentials): ?string
    {
        $serviceAccount = new ServiceAccountCredentials(self::INDEXING_SCOPE, $credentials);
        $token = $serviceAccount->fetchAuthToken();

        return $token['access_token'] ?? null;
    }
}
