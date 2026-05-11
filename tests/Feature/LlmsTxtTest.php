<?php

namespace Tests\Feature;

use App\Models\Post\Posts;
use App\Models\Settings\GeneralSettings;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LlmsTxtTest extends TestCase
{
    public function test_general_settings_has_llms_txt_intro_column(): void
    {
        $this->assertTrue(
            in_array('llms_txt_intro', (new GeneralSettings)->getFillable())
        );
        $this->assertTrue(
            in_array('llms_txt_instructions', (new GeneralSettings)->getFillable())
        );
    }

    public function test_llms_txt_returns_200_with_plain_text(): void
    {
        $response = $this->get('/llms.txt');
        $response->assertOk();
        $this->assertStringContainsString('text/plain', $response->headers->get('Content-Type'));
    }

    public function test_llms_full_txt_returns_200_with_plain_text(): void
    {
        $response = $this->get('/llms-full.txt');
        $response->assertOk();
        $this->assertStringContainsString('text/plain', $response->headers->get('Content-Type'));
    }

    public function test_llms_txt_starts_with_h1(): void
    {
        $response = $this->get('/llms.txt');
        $this->assertStringStartsWith('# ', $response->getContent());
    }

    public function test_llms_txt_includes_published_post_title(): void
    {
        Cache::forget('llms_txt_content');

        $post = Posts::factory()->create([
            'is_published' => true,
            'post_type' => 'post',
            'title' => 'My Test Article For LLMs',
        ]);

        $response = $this->get('/llms.txt');
        $response->assertSee('My Test Article For LLMs');

        $post->forceDelete();
    }

    public function test_llms_txt_excludes_unpublished_posts(): void
    {
        Cache::forget('llms_txt_content');

        $post = Posts::factory()->create([
            'is_published' => false,
            'post_type' => 'post',
            'title' => 'Draft Post Should Not Appear',
        ]);

        $response = $this->get('/llms.txt');
        $response->assertDontSee('Draft Post Should Not Appear');

        $post->forceDelete();
    }

    public function test_llms_full_txt_includes_post_body(): void
    {
        Cache::forget('llms_full_txt_content');

        $post = Posts::factory()->create([
            'is_published' => true,
            'post_type' => 'post',
            'title' => 'Full Content Test',
            'content' => '<p>Unique content for llms full test.</p>',
        ]);

        $response = $this->get('/llms-full.txt');
        $response->assertSee('Unique content for llms full test.');

        $post->forceDelete();
    }

    public function test_llms_txt_uses_cache(): void
    {
        Cache::forget('llms_txt_content');

        $this->get('/llms.txt');

        $this->assertTrue(Cache::has('llms_txt_content'));
    }

    public function test_admin_can_save_llms_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson(route('admin.settings.seo.llms.save'), [
            'llms_txt_intro' => 'We are a blog about tech.',
            'llms_txt_instructions' => 'Please index all posts.',
        ]);

        $response->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('general_settings', [
            'llms_txt_intro' => 'We are a blog about tech.',
        ]);
    }

    public function test_admin_can_clear_llms_cache(): void
    {
        Cache::forever('llms_txt_content', 'cached-content');
        Cache::forever('llms_full_txt_content', 'cached-full-content');

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson(route('admin.settings.seo.llms.clear-cache'));

        $response->assertJson(['status' => 'success']);
        $this->assertNull(Cache::get('llms_txt_content'));
        $this->assertNull(Cache::get('llms_full_txt_content'));
    }

    public function test_guest_cannot_save_llms_settings(): void
    {
        $response = $this->postJson(route('admin.settings.seo.llms.save'), [
            'llms_txt_intro' => 'hacked',
        ]);
        $response->assertUnauthorized();
    }
}
