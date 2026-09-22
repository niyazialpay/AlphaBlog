<?php

namespace Tests\Feature\Panel;

use Inertia\Testing\AssertableInertia;

class PostDataTableCompatTest extends PanelTestCase
{
    public function test_legacy_datatable_request_still_receives_json(): void
    {
        $this->migrateScreens(['admin.posts']);

        $response = $this->actingAs($this->owner)->get(
            route('admin.posts', ['type' => 'blogs']).'?draw=1&start=0&length=10',
            ['X-Requested-With' => 'XMLHttpRequest'],
        );

        $response->assertOk();
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }

    public function test_inertia_visit_receives_inertia_response(): void
    {
        $this->migrateScreens(['admin.posts']);

        $response = $this->actingAs($this->owner)->get(
            route('admin.posts', ['type' => 'blogs']),
            $this->inertiaHeaders(),
        );

        $response->assertOk();
        $response->assertHeader('X-Inertia', 'true');
        $response->assertJsonPath('component', 'Posts/Index');
    }

    public function test_full_page_load_renders_inertia_component(): void
    {
        $this->migrateScreens(['admin.posts']);

        $this->actingAs($this->owner)
            ->get(route('admin.posts', ['type' => 'blogs']))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Posts/Index'));
    }

    public function test_inertia_visit_does_not_clobber_datatable_length_session(): void
    {
        $this->migrateScreens(['admin.posts']);

        $this->withSession(['post_datatable_length' => 75])
            ->actingAs($this->owner)
            ->get(route('admin.posts', ['type' => 'blogs']), $this->inertiaHeaders())
            ->assertOk();

        $this->assertSame(75, session('post_datatable_length'));
    }
}
