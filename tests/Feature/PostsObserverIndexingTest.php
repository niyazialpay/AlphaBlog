<?php

namespace Tests\Feature;

use App\Jobs\SubmitUrlToGoogleIndex;
use App\Models\Post\PostIndexingLog;
use App\Models\Post\Posts;
use App\Models\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class PostsObserverIndexingTest extends TestCase
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

    public function test_publishing_new_post_dispatches_job(): void
    {
        Bus::fake();

        $post = Posts::factory()->create(['is_published' => false]);
        $post->is_published = true;
        $post->save();

        Bus::assertDispatched(SubmitUrlToGoogleIndex::class, fn ($job) => $job->post->id === $post->id);
    }

    public function test_saving_draft_does_not_dispatch_job(): void
    {
        Bus::fake();

        Posts::factory()->create(['is_published' => false]);

        Bus::assertNotDispatched(SubmitUrlToGoogleIndex::class);
    }

    public function test_re_saving_published_post_without_status_change_does_not_dispatch(): void
    {
        $post = Posts::factory()->create(['is_published' => true]);
        PostIndexingLog::create([
            'post_id' => $post->id,
            'url' => 'https://x.com',
            'type' => 'URL_UPDATED',
            'status' => 'success',
            'response_code' => 200,
        ]);

        Bus::fake();

        $post->title = 'Updated title';
        $post->save();

        Bus::assertNotDispatched(SubmitUrlToGoogleIndex::class);
    }

    public function test_indexing_disabled_does_not_dispatch_job(): void
    {
        Bus::fake();
        GeneralSettings::first()->update(['google_indexing_enabled' => false]);

        $post = Posts::factory()->create(['is_published' => false]);
        $post->is_published = true;
        $post->save();

        Bus::assertNotDispatched(SubmitUrlToGoogleIndex::class);
    }
}
