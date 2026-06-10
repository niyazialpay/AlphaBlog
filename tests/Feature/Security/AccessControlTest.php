<?php

namespace Tests\Feature\Security;

use App\Http\Middleware\FirewallMiddleware;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\Language;
use App\Http\Middleware\VerifyOTP;
use App\Models\User;
use App\Models\UserSessions;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            VerifyOTP::class,
            EnsureEmailIsVerified::class,
            Language::class,
            HandleInertiaRequests::class,
            FirewallMiddleware::class,
        ]);
    }

    /** ALPHA-001: a low-privilege user must not be able to escalate role via profile save. */
    public function test_user_cannot_escalate_role_via_profile_save(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->postJson(route('admin.profile.save'), [
                'name' => 'Mallory',
                'surname' => 'Attacker',
                'nickname' => 'mallory',
                'role' => 'owner',
            ])
            ->assertOk();

        $this->assertSame('user', $user->fresh()->role, 'role must NOT change via profile save');
    }

    /** ALPHA-001: the profile save itself still works for legitimate fields. */
    public function test_profile_save_updates_allowed_fields(): void
    {
        $user = User::factory()->create(['role' => 'user', 'name' => 'Old']);

        $this->actingAs($user)
            ->postJson(route('admin.profile.save'), [
                'name' => 'NewName',
                'surname' => 'NewSurname',
                'nickname' => 'newnick',
            ])
            ->assertOk();

        $this->assertSame('NewName', $user->fresh()->name);
    }

    /** ALPHA-010: an admin must not be able to promote themselves to owner via user-edit. */
    public function test_admin_cannot_self_promote_to_owner_via_user_edit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $url = '/'.config('settings.admin_panel_path').'/users/'.$admin->id.'/edit';

        $this->actingAs($admin)->post($url, [
            'name' => 'Admin',
            'surname' => 'User',
            'nickname' => 'adminuser',
            'role' => 'owner',
        ]);

        $this->assertSame('admin', $admin->fresh()->role, 'admin must NOT be able to grant owner');
    }

    /** ALPHA-010: an admin MAY still assign a role strictly below their own. */
    public function test_admin_can_assign_lower_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'user']);
        $url = '/'.config('settings.admin_panel_path').'/users/'.$target->id.'/edit';

        $this->actingAs($admin)->post($url, [
            'name' => 'Some',
            'surname' => 'Author',
            'nickname' => 'someauthor',
            'role' => 'author',
        ]);

        $this->assertSame('author', $target->fresh()->role);
    }

    /** ALPHA-011: a user must not be able to kill another user's session (broken ownAdmin policy). */
    public function test_user_cannot_kill_another_users_session(): void
    {
        $attacker = User::factory()->create(['role' => 'user']);
        $victim = User::factory()->create(['role' => 'user']);

        $victimSession = UserSessions::create([
            'user_id' => $victim->id,
            'session_id' => 'victim-session-id',
            'ip_address' => '10.0.0.1',
            'user_agent' => 'phpunit',
        ]);

        $this->actingAs($attacker)
            ->postJson(route('user.session.logout'), ['session_id' => $victimSession->id])
            ->assertForbidden();

        $this->assertDatabaseHas('user_sessions', ['id' => $victimSession->id]);
    }

    /** ALPHA-012: an admin must not be able to impersonate the owner (no role ceiling). */
    public function test_admin_cannot_impersonate_owner(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($admin)
            ->get(route('admin.user.secret-login', ['user_id' => $owner->id]))
            ->assertForbidden();

        $this->assertSame($admin->id, auth()->id(), 'session must remain the admin, not the owner');
    }

    /** ALPHA-012: impersonating a strictly-lower role still works. */
    public function test_admin_can_impersonate_lower_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)
            ->get(route('admin.user.secret-login', ['user_id' => $target->id]))
            ->assertRedirect();

        $this->assertSame($target->id, auth()->id());
    }
}
