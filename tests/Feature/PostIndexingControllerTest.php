<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\VerifyOTP;
use App\Jobs\SubmitUrlToGoogleIndex;
use App\Models\Languages;
use App\Models\Post\PostIndexingLog;
use App\Models\Post\Posts;
use App\Models\Settings\GeneralSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostIndexingControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([HandleInertiaRequests::class, VerifyOTP::class]);
        Bus::fake();
        DB::table('languages')->insert([
            'name' => 'Türkçe', 'code' => 'tr', 'flag' => 'tr',
            'is_active' => true, 'is_default' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $lang = Languages::first();
        app()->instance('default_language', $lang);
        app()->instance('languages', collect([$lang]));
        GeneralSettings::firstOrCreate([], [
            'google_indexing_enabled' => true,
            'google_indexing_daily_limit' => 200,
        ]);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_bulk_index_queues_unindexed_published_posts(): void
    {
        Bus::fake();

        $published = Posts::factory()->count(3)->create(['is_published' => true]);
        $draft = Posts::factory()->create(['is_published' => false]);

        $ids = $published->pluck('id')->concat([$draft->id])->toArray();

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.post.index.bulk', 'blogs'), ['post_ids' => $ids]);

        $response->assertOk()
            ->assertJson(['status' => 'success', 'queued' => 3, 'skipped' => 1]);

        Bus::assertDispatchedTimes(SubmitUrlToGoogleIndex::class, 3);
    }

    public function test_bulk_index_skips_already_indexed_posts(): void
    {
        Bus::fake();

        $indexed = Posts::factory()->create(['is_published' => true]);
        PostIndexingLog::create(['post_id' => $indexed->id, 'url' => 'https://x.com', 'type' => 'URL_UPDATED', 'status' => 'success', 'response_code' => 200]);

        $fresh = Posts::factory()->create(['is_published' => true]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.post.index.bulk', 'blogs'), ['post_ids' => [$indexed->id, $fresh->id]]);

        $response->assertOk()
            ->assertJson(['status' => 'success', 'queued' => 1, 'skipped' => 1]);
    }

    public function test_single_dispatches_with_force_flag(): void
    {
        Bus::fake();

        $post = Posts::factory()->create(['is_published' => true]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.post.index.single', ['type' => 'blogs', 'post' => $post]));

        $response->assertOk()->assertJson(['status' => 'success']);
        Bus::assertDispatched(SubmitUrlToGoogleIndex::class);
    }

    public function test_history_returns_indexing_logs(): void
    {
        $post = Posts::factory()->create(['is_published' => true]);

        PostIndexingLog::create(['post_id' => $post->id, 'url' => 'https://x.com', 'type' => 'URL_UPDATED', 'status' => 'success', 'response_code' => 200]);
        PostIndexingLog::create(['post_id' => $post->id, 'url' => 'https://x.com', 'type' => 'URL_UPDATED', 'status' => 'failed', 'response_code' => 403, 'message' => 'Permission denied']);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.post.index.history', ['type' => 'blogs', 'post' => $post]));

        $response->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment(['status' => 'success'])
            ->assertJsonFragment(['status' => 'failed']);
    }

    public function test_bulk_index_requires_authentication(): void
    {
        $post = Posts::factory()->create(['is_published' => true]);

        $this->postJson(route('admin.post.index.bulk', 'blogs'), ['post_ids' => [$post->id]])
            ->assertUnauthorized();
    }
}
