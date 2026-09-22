<?php

namespace Tests\Feature\Panel;

use App\Models\User;

/**
 * Yazi editorundeki yazar alaninin sunucu tarafli aramasi (`admin.authors.search`).
 */
class AuthorSearchTest extends PanelTestCase
{
    /**
     * @return list<array{value: string, label: string, description: string|null}>
     */
    private function search(User $as, string $query): array
    {
        return $this->actingAs($as)
            ->getJson(route('admin.authors.search', ['q' => $query]))
            ->assertOk()
            ->json('data');
    }

    private function author(array $attributes): User
    {
        return User::factory()->create($attributes + ['role' => 'author', 'otp' => false, 'webauthn' => false]);
    }

    public function test_matches_name_surname_and_nickname_across_words(): void
    {
        $target = $this->author(['name' => 'Ahmet', 'surname' => 'Rıza', 'nickname' => 'arz']);
        $this->author(['name' => 'Mehmet', 'surname' => 'Kaya', 'nickname' => 'mk']);

        $this->assertSame([(string) $target->id], array_column($this->search($this->owner, 'Rıza Ahmet'), 'value'));
        $this->assertSame([(string) $target->id], array_column($this->search($this->owner, 'arz'), 'value'));
    }

    public function test_email_is_searchable_and_visible_only_for_admins(): void
    {
        $target = $this->author(['name' => 'Ayse', 'surname' => 'Demir', 'email' => 'gizli@example.test']);
        $editor = $this->author(['role' => 'editor']);

        $asOwner = $this->search($this->owner, 'gizli@example');
        $this->assertSame([(string) $target->id], array_column($asOwner, 'value'));
        $this->assertStringContainsString('gizli@example.test', $asOwner[0]['description']);

        $this->assertSame([], $this->search($editor, 'gizli@example'));

        $asEditor = collect($this->search($editor, 'Demir'))->firstWhere('value', (string) $target->id);
        $this->assertNotNull($asEditor);
        $this->assertStringNotContainsString('gizli@example.test', (string) $asEditor['description']);
    }

    public function test_like_wildcards_are_literal(): void
    {
        $this->author(['name' => 'Zeynep', 'surname' => 'Ak']);

        $this->assertSame([], $this->search($this->owner, '%'));
        $this->assertSame([], $this->search($this->owner, '_'));
    }

    public function test_users_without_post_permission_are_forbidden(): void
    {
        $member = $this->author(['role' => 'user']);

        $this->actingAs($member)
            ->getJson(route('admin.authors.search', ['q' => 'a']))
            ->assertForbidden();
    }

    public function test_guests_cannot_search(): void
    {
        $this->getJson(route('admin.authors.search', ['q' => 'a']))
            ->assertUnauthorized();
    }
}
