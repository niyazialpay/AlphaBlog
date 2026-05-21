<?php

namespace Tests\Feature;

use App\Actions\GoogleIndexingAction;
use App\Models\Post\PostIndexingLog;
use App\Models\Post\Posts;
use App\Models\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleIndexingActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        GeneralSettings::firstOrCreate([], [
            'google_indexing_enabled' => true,
            'google_indexing_daily_limit' => 200,
        ]);
    }

    public function test_submit_returns_failed_when_no_credentials(): void
    {
        // Ensure the credentials file does not exist in test environment
        $credPath = storage_path('app/analytics/service-account-credentials.json');
        $exists = file_exists($credPath);
        if ($exists) {
            rename($credPath, $credPath.'.bak');
        }

        try {
            $action = new GoogleIndexingAction;
            $result = $action->submit('https://example.com/test');

            $this->assertSame('failed', $result['status']);
            $this->assertStringContainsString('credentials', strtolower($result['message']));
        } finally {
            if ($exists) {
                rename($credPath.'.bak', $credPath);
            }
        }
    }

    public function test_daily_quota_not_reached_when_no_logs(): void
    {
        $action = new GoogleIndexingAction;
        $this->assertFalse($action->dailyQuotaReached());
    }

    public function test_daily_quota_reached_when_limit_exceeded(): void
    {
        GeneralSettings::first()->update(['google_indexing_daily_limit' => 2]);

        $post = Posts::factory()->create(['is_published' => true]);

        PostIndexingLog::insert([
            ['post_id' => $post->id, 'url' => 'https://x.com/a', 'type' => 'URL_UPDATED', 'status' => 'success', 'response_code' => 200, 'message' => null, 'created_at' => now()],
            ['post_id' => $post->id, 'url' => 'https://x.com/b', 'type' => 'URL_UPDATED', 'status' => 'success', 'response_code' => 200, 'message' => null, 'created_at' => now()],
        ]);

        $action = new GoogleIndexingAction;
        $this->assertTrue($action->dailyQuotaReached());
    }

    public function test_daily_quota_not_reached_for_old_logs(): void
    {
        GeneralSettings::first()->update(['google_indexing_daily_limit' => 1]);

        $post = Posts::factory()->create(['is_published' => true]);

        PostIndexingLog::insert([
            ['post_id' => $post->id, 'url' => 'https://x.com/a', 'type' => 'URL_UPDATED', 'status' => 'success', 'response_code' => 200, 'message' => null, 'created_at' => now()->subDay()],
        ]);

        $action = new GoogleIndexingAction;
        $this->assertFalse($action->dailyQuotaReached());
    }
}
