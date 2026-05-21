<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyOTP;
use App\Models\DashboardWidget;
use App\Models\Languages;
use App\Models\Settings\GeneralSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardWidgetTest extends TestCase
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

    public function test_user_starts_with_empty_dashboard(): void
    {
        $this->assertSame(0, $this->admin->dashboardWidgets()->count());
    }

    public function test_save_widgets_stores_widgets_for_user(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.dashboard.widgets.save'), [
                'layout' => [
                    ['type' => 'ga4_active_users', 'x' => 0, 'y' => 0, 'w' => 3, 'h' => 2],
                    ['type' => 'gsc_clicks', 'x' => 3, 'y' => 0, 'w' => 3, 'h' => 2],
                ],
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertSame(2, $this->admin->dashboardWidgets()->count());

        $widget = $this->admin->dashboardWidgets()->where('widget_type', 'ga4_active_users')->first();
        $this->assertNotNull($widget);
        $this->assertSame(0, $widget->gs_x);
        $this->assertSame(0, $widget->gs_y);
        $this->assertSame(3, $widget->gs_w);
        $this->assertSame(2, $widget->gs_h);
    }

    public function test_save_widgets_replaces_existing(): void
    {
        DashboardWidget::create([
            'user_id' => $this->admin->id,
            'widget_type' => 'ga4_active_users',
            'gs_x' => 0, 'gs_y' => 0, 'gs_w' => 3, 'gs_h' => 2,
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.dashboard.widgets.save'), [
                'layout' => [
                    ['type' => 'gsc_clicks', 'x' => 0, 'y' => 0, 'w' => 3, 'h' => 2],
                ],
            ])
            ->assertOk();

        $this->assertSame(1, $this->admin->dashboardWidgets()->count());
        $this->assertSame('gsc_clicks', $this->admin->dashboardWidgets()->first()->widget_type);
    }

    public function test_widgets_are_isolated_per_user(): void
    {
        $otherUser = User::factory()->create(['role' => 'admin']);

        DashboardWidget::create([
            'user_id' => $this->admin->id,
            'widget_type' => 'ga4_active_users',
            'gs_x' => 0, 'gs_y' => 0, 'gs_w' => 3, 'gs_h' => 2,
        ]);

        $this->assertSame(0, $otherUser->dashboardWidgets()->count());
    }

    public function test_unauthenticated_cannot_save_widgets(): void
    {
        $this->postJson(route('admin.dashboard.widgets.save'), ['layout' => []])
            ->assertUnauthorized();
    }

    public function test_dashboard_index_loads_for_admin(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.index'));
        $response->assertOk();
        $response->assertViewIs('panel.dashboard');
        $response->assertViewHas('widgets');
        $response->assertViewHas('widgetData');
        $response->assertViewHas('widgetGroups');
    }
}
