<?php

namespace Tests\Feature\Panel;

use App\Models\Post\Categories;
use App\Models\User;

class PostEditorQuickCreateTest extends PanelTestCase
{
    public function test_category_store_returns_json_for_the_editor_modal(): void
    {
        $response = $this->actingAs($this->owner)
            ->postJson(route('admin.categories'), [
                'name' => 'Yeni Kategori',
                'slug' => 'yeni-kategori',
                'language' => 'tr',
                'parent_id' => null,
            ])
            ->assertOk()
            ->assertJsonPath('category.name', 'Yeni Kategori')
            ->assertJsonPath('category.language', 'tr');

        $this->assertTrue(Categories::whereKey($response->json('category.id'))->exists());
    }

    public function test_category_store_returns_validation_errors_as_json(): void
    {
        $this->actingAs($this->owner)
            ->postJson(route('admin.categories'), ['slug' => 'x', 'language' => 'tr'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_category_store_still_redirects_for_inertia_forms(): void
    {
        $this->actingAs($this->owner)
            ->from(route('admin.categories'))
            ->post(route('admin.categories'), ['name' => 'Form', 'slug' => 'form', 'language' => 'tr'], $this->inertiaHeaders())
            ->assertRedirect(route('admin.categories'));
    }

    public function test_user_store_returns_author_option_as_json(): void
    {
        $response = $this->actingAs($this->owner)
            ->postJson(route('admin.user.create'), [
                'name' => 'Ayse',
                'surname' => 'Demir',
                'nickname' => 'ayse',
                'username' => 'ayse',
                'email' => 'ayse@example.test',
                'role' => 'author',
                'password' => 'correct-horse-battery',
                'password_confirmation' => 'correct-horse-battery',
            ])
            ->assertOk()
            ->assertJsonPath('option.label', 'Ayse Demir (ayse)');

        $user = User::where('email', 'ayse@example.test')->firstOrFail();
        $this->assertSame((string) $user->id, $response->json('option.value'));
        $this->assertSame('author', $user->role);
    }

    public function test_user_store_rejects_roles_above_the_actor_as_json(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'otp' => false, 'webauthn' => false]);

        $this->actingAs($admin)
            ->postJson(route('admin.user.create'), [
                'name' => 'X',
                'surname' => 'Y',
                'nickname' => 'xy',
                'username' => 'xy',
                'email' => 'xy@example.test',
                'role' => 'owner',
                'password' => 'correct-horse-battery',
                'password_confirmation' => 'correct-horse-battery',
            ])
            ->assertForbidden();

        $this->assertFalse(User::where('email', 'xy@example.test')->exists());
    }
}
