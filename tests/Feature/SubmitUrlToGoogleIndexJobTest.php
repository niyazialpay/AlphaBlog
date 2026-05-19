<?php

namespace Tests\Feature;

use App\Actions\GoogleIndexingAction;
use App\Jobs\SubmitUrlToGoogleIndex;
use App\Models\Post\PostIndexingLog;
use App\Models\Post\Posts;
use App\Models\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SubmitUrlToGoogleIndexJobTest extends TestCase
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

    public function test_job_skips_when_indexing_disabled(): void
    {
        GeneralSettings::first()->update(['google_indexing_enabled' => false]);

        $post = Posts::factory()->create(['is_published' => true]);
        $action = $this->createMock(GoogleIndexingAction::class);
        $action->expects($this->never())->method('submit');

        $job = new SubmitUrlToGoogleIndex($post);
        $job->handle($action);

        $this->assertDatabaseCount('post_indexing_logs', 0);
    }

    public function test_job_skips_already_indexed_post_without_force(): void
    {
        $post = Posts::factory()->create(['is_published' => true]);
        PostIndexingLog::create([
            'post_id' => $post->id,
            'url' => 'https://x.com',
            'type' => 'URL_UPDATED',
            'status' => 'success',
            'response_code' => 200,
        ]);

        $action = $this->createMock(GoogleIndexingAction::class);
        $action->expects($this->never())->method('submit');

        $job = new SubmitUrlToGoogleIndex($post, 'URL_UPDATED', false);
        $job->handle($action);

        $this->assertDatabaseCount('post_indexing_logs', 1);
    }

    public function test_job_proceeds_with_force_even_if_indexed(): void
    {
        $post = Posts::factory()->create(['is_published' => true, 'language' => 'tr', 'slug' => 'test-post']);
        PostIndexingLog::create([
            'post_id' => $post->id,
            'url' => 'https://x.com',
            'type' => 'URL_UPDATED',
            'status' => 'success',
            'response_code' => 200,
        ]);

        $action = $this->createMock(GoogleIndexingAction::class);
        $action->method('dailyQuotaReached')->willReturn(false);
        $action->expects($this->once())->method('submit')->willReturn(['status' => 'success', 'code' => 200, 'message' => '{}']);

        $job = new SubmitUrlToGoogleIndex($post, 'URL_UPDATED', true);
        $job->handle($action);

        $this->assertDatabaseCount('post_indexing_logs', 2);
    }

    public function test_job_writes_success_log(): void
    {
        $post = Posts::factory()->create(['is_published' => true, 'language' => 'tr', 'slug' => 'my-test-post']);

        $action = $this->createMock(GoogleIndexingAction::class);
        $action->method('dailyQuotaReached')->willReturn(false);
        $action->method('submit')->willReturn(['status' => 'success', 'code' => 200, 'message' => '{}']);

        $job = new SubmitUrlToGoogleIndex($post);
        $job->handle($action);

        $this->assertDatabaseHas('post_indexing_logs', [
            'post_id' => $post->id,
            'type' => 'URL_UPDATED',
            'status' => 'success',
            'response_code' => 200,
        ]);
    }

    public function test_job_writes_failed_log_on_exception(): void
    {
        $post = Posts::factory()->create(['is_published' => true, 'language' => 'tr', 'slug' => 'fail-post']);

        $job = new SubmitUrlToGoogleIndex($post);
        $job->failed(new \RuntimeException('Connection timeout'));

        $this->assertDatabaseHas('post_indexing_logs', [
            'post_id' => $post->id,
            'status' => 'failed',
            'message' => 'Connection timeout',
        ]);
    }

    public function test_job_redispatches_when_quota_reached(): void
    {
        Bus::fake();

        $post = Posts::factory()->create(['is_published' => true]);

        $action = $this->createMock(GoogleIndexingAction::class);
        $action->method('dailyQuotaReached')->willReturn(true);
        $action->expects($this->never())->method('submit');

        $job = new SubmitUrlToGoogleIndex($post);
        $job->handle($action);

        Bus::assertDispatched(SubmitUrlToGoogleIndex::class);
        $this->assertDatabaseCount('post_indexing_logs', 0);
    }
}
