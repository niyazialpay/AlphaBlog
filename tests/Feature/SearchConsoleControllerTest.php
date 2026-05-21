<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyOTP;
use App\Models\Languages;
use App\Models\Settings\GeneralSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SearchConsoleControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([VerifyOTP::class]);
        DB::table('languages')->insert([
            'name' => 'Türkçe', 'code' => 'tr', 'flag' => 'tr',
            'is_active' => true, 'is_default' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $lang = Languages::first();
        app()->instance('default_language', $lang);
        app()->instance('languages', collect([$lang]));
        GeneralSettings::firstOrCreate([], []);
        $this->admin = User::factory()->create(['role' => 'owner']);
    }

    public function test_unauthenticated_redirects_from_index(): void
    {
        $response = $this->get(route('admin.search-console'));
        $response->assertRedirect();
    }

    public function test_index_returns_view_when_not_configured(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.search-console'));
        $response->assertOk();
        $response->assertViewIs('panel.search-console');
        $response->assertViewHas('configured', false);
    }

    public function test_fetch_returns_json(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.search-console.fetch'), []);
        $response->assertOk();
        $response->assertJsonStructure(['configured', 'date_range', 'performance', 'keywords', 'trend']);
    }

    public function test_fetch_returns_not_configured_when_no_file(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.search-console.fetch'), []);
        $response->assertOk();
        $response->assertJson(['configured' => false]);
    }
}
