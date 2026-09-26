<?php

namespace Tests\Feature\Panel;

use App\Models\Post\Comments;
use App\Models\Post\Posts;
use App\Models\User;

class PostBulkDeleteTest extends PanelTestCase
{
    public function test_owner_soft_deletes_selected_posts_and_their_comments(): void
    {
        $selected = Posts::factory()->count(2)->create();
        $untouched = Posts::factory()->create();
        $comment = Comments::query()->create([
            'post_id' => $selected->first()->id,
            'comment' => 'Test',
            'name' => 'Test',
            'email' => 'test@example.com',
            'is_approved' => true,
        ]);

        $this->actingAs($this->owner)
            ->from(route('admin.posts', 'blogs'))
            ->post(route('admin.post.delete.bulk', 'blogs'), ['post_ids' => $selected->modelKeys()])
            ->assertRedirect(route('admin.posts', 'blogs'))
            ->assertSessionHas('success');

        foreach ($selected as $post) {
            $this->assertSoftDeleted($post);
        }

        $this->assertNotSoftDeleted($untouched);
        $this->assertSoftDeleted($comment);
    }

    public function test_author_deletes_only_own_posts_and_others_are_skipped(): void
    {
        $author = User::factory()->create(['role' => 'author', 'otp' => false, 'webauthn' => false]);
        $own = Posts::factory()->create(['user_id' => $author->id]);
        $foreign = Posts::factory()->create();

        $this->actingAs($author)
            ->from(route('admin.posts', 'blogs'))
            ->post(route('admin.post.delete.bulk', 'blogs'), ['post_ids' => [$own->id, $foreign->id]])
            ->assertSessionHas('success', __('post.bulk_delete_result', ['deleted' => 1, 'skipped' => 1]));

        $this->assertSoftDeleted($own);
        $this->assertNotSoftDeleted($foreign);
    }

    public function test_nothing_is_deleted_when_user_may_delete_none_of_the_posts(): void
    {
        $author = User::factory()->create(['role' => 'author', 'otp' => false, 'webauthn' => false]);
        $foreign = Posts::factory()->create();

        $this->actingAs($author)
            ->from(route('admin.posts', 'blogs'))
            ->post(route('admin.post.delete.bulk', 'blogs'), ['post_ids' => [$foreign->id]])
            ->assertSessionHas('error', __('post.bulk_delete_none'));

        $this->assertNotSoftDeleted($foreign);
    }

    public function test_post_ids_are_required(): void
    {
        $this->actingAs($this->owner)
            ->from(route('admin.posts', 'blogs'))
            ->post(route('admin.post.delete.bulk', 'blogs'), [])
            ->assertSessionHasErrors('post_ids');
    }
}
