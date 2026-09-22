<?php

namespace Tests\Feature\Panel;

use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;

class PanelInertiaSmokeTest extends PanelTestCase
{
    /**
     * @return array<string, array{0: string, 1: array<string, mixed>, 2: string}>
     */
    public static function panelPages(): array
    {
        return [
            'about' => ['admin.about', [], 'About/Index'],

            'monitoring.pulse' => ['admin.monitoring.pulse', [], 'Monitoring/Index'],
            'monitoring.telescope' => ['admin.monitoring.telescope', [], 'Monitoring/Index'],
            'monitoring.horizon' => ['admin.monitoring.horizon', [], 'Monitoring/Index'],
            'monitoring.logs' => ['admin.monitoring.logs', [], 'Monitoring/Index'],
            'notifications' => ['notifications.index', [], 'Notifications/Index'],
            'contact.messages' => ['admin.contact_messages', [], 'Contact/Messages'],
            'redirects' => ['adminRoutes', [], 'Redirects/Index'],
            'system-logs' => ['admin.system-logs', [], 'Logs/Index'],
            'search-words' => ['admin.search.index', [], 'SearchWords/Index'],

            'posts.blogs' => ['admin.posts', ['type' => 'blogs'], 'Posts/Index'],
            'posts.pages' => ['admin.posts', ['type' => 'pages'], 'Posts/Index'],
            'posts.create' => ['admin.post.create', ['type' => 'blogs'], 'Posts/Edit'],
            'categories' => ['admin.categories', [], 'Categories/Index'],
            'comments' => ['admin.post.comments', [], 'Comments/Index'],

            'users' => ['admin.users', [], 'Users/Index'],
            'users.create' => ['admin.user.create', [], 'Users/Create'],
            'notes' => ['admin.notes', [], 'Notes/Encryption'],
            'notes.categories' => ['admin.notes.categories', [], 'Notes/Encryption'],
            'menu' => ['admin.menu.index', [], 'Menu/Index'],
            'profile' => ['admin.profile.index', [], 'Profile/Index'],

            'settings' => ['admin.settings', [], 'Settings/Index'],
            'ip-filter' => ['admin.ip-filter', [], 'IpFilter/Index'],
            'ip-filter.create' => ['admin.ip-filter.create', [], 'IpFilter/Show'],
            'firewall' => ['admin.firewall', [], 'Firewall/Index'],
            'firewall.logs' => ['admin.firewall.logs', [], 'Firewall/Logs'],

            'dashboard' => ['admin.index', [], 'Dashboard/Index'],
            'contact.page' => ['admin.contact_page', [], 'Contact/Page'],
            'analytics' => ['admin.analytics', [], 'Analytics/Index'],
            'search-console' => ['admin.search-console', [], 'SearchConsole/Index'],
            'chatbot' => ['chatbot', [], 'Chat/Index'],
        ];
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    #[DataProvider('panelPages')]
    public function test_panel_page_renders_expected_inertia_component(
        string $routeName,
        array $parameters,
        string $component,
    ): void {
        $this->migrateScreens([$routeName]);

        if (str_starts_with($routeName, 'admin.firewall')) {
            $this->seedFirewall();
        }

        $this->actingAs($this->owner)
            ->get(route($routeName, $parameters))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component($component));
    }

    public function test_blade_kill_switch_falls_back_to_legacy_shell(): void
    {
        $this->migrateScreens(['admin.about']);
        config()->set('settings.panel_ui', 'blade');

        $response = $this->actingAs($this->owner)->get(route('admin.about'));

        $response->assertOk();
        $response->assertViewIs('panel.about');
    }

    public function test_screen_allowlist_limits_vue_surface(): void
    {
        $this->migrateScreens(['admin.about']);
        config()->set('settings.panel_ui_screens', 'Dashboard');

        $this->actingAs($this->owner)
            ->get(route('admin.about'))
            ->assertOk()
            ->assertViewIs('panel.about');
    }
}
