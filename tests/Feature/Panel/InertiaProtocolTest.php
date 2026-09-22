<?php

namespace Tests\Feature\Panel;

use App\Models\Post\Posts;

class InertiaProtocolTest extends PanelTestCase
{
    public function test_inertia_visit_to_posts_does_not_return_datatables_json(): void
    {
        $this->migrateScreens(['admin.posts']);

        $response = $this->actingAs($this->owner)
            ->withHeaders($this->inertiaHeaders())
            ->get(route('admin.posts', ['type' => 'blogs']));

        $content = $response->getContent();

        $this->assertStringNotContainsString(
            '"recordsTotal"',
            (string) $content,
            'Inertia ziyareti DataTables beslemesine düştü.',
        );
    }

    public function test_legacy_datatables_request_still_returns_feed(): void
    {
        Posts::factory()?->count(0);

        $response = $this->actingAs($this->owner)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('admin.posts', ['type' => 'blogs', 'draw' => 1, 'start' => 0, 'length' => 10]));

        $response->assertOk();
        $this->assertStringContainsString('recordsTotal', (string) $response->getContent());
    }

    public function test_otp_wall_returns_inertia_response_for_inertia_visits(): void
    {
        $this->migrateScreens(['admin.about']);

        $this->owner->forceFill(['otp' => true])->save();

        $this->actingAs($this->owner)
            ->withHeaders($this->inertiaHeaders())
            ->get(route('admin.about'))
            ->assertOk()
            ->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Auth/Otp');
    }

    public function test_otp_wall_keeps_blade_for_full_page_loads(): void
    {
        $this->migrateScreens(['admin.about']);

        $this->owner->forceFill(['otp' => true])->save();

        $this->actingAs($this->owner)
            ->get(route('admin.about'))
            ->assertOk()
            ->assertViewIs('panel.auth.otp');
    }
}
