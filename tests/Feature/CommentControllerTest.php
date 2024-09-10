<?php

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public static function commentsProvider(): array
    {
        return [
            'post comments' => [null],
            'comment replies' => [fn () => Comment::factory()->create()->id],
        ];
    }

    public static function commentCreateProvider(): array
    {
        return [
            'create comment' => [null],
            'create reply' => [fn () => Comment::factory()->create()],
        ];
    }

    public static function commentReadProvider(): array
    {
        return [
            'post comments' => [null, fn ($slug) => "/api/posts/{$slug}/comments"],
            'comment replies' => [fn () => Comment::factory()->create(), fn ($id) => "/api/comments/{$id}/replies"]
        ];
    }

    public static function commentGeneralProvider(): array
    {
        return [
            'comment' => [fn ($userId) => Comment::factory()->create([
                'user_id' => $userId,
                'parent_id' => null
            ])],
            'reply' => [fn ($userId) => Comment::factory()->create([
                'user_id' => $userId,
                'parent_id' => Comment::factory()->create()
            ])],
        ];
    }

    #[DataProvider('commentReadProvider')]
    public function test_can_retrieve_comments($parent = null, $url)
    {
        $parent && $parent = $parent();

        $post = Post::factory()->create();

        Comment::factory(5)->create([
            'post_id' => $post->id,
            'parent_id' => $parent->id ?? null
        ]);

        $response = $this->getJson($url(!$parent ? $post->slug : $parent->id));

        $response->assertOk();
        $response->assertJsonStructure(
            [
                '*' => [
                    'id', 'content', 'user_id', 'post_id', 'parent_id', 'favors_count', 'opposes_count', 'comments_count', 'reacted_by_logged_user'
                ]
            ]
        );
    }

    #[DataProvider('commentCreateProvider')]
    public function test_authenticated_user_can_create_comment($parent = null): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $post = Post::factory()->create();

        if (is_callable($parent))
        {
            $parent = $parent();
        }

        $response = $this->postJson('/api/comments', [
            'content' => 'Test comment',
            'user_id' => $user->id,
            'parent_id' => $parent ? $parent->id : null,
            'post_id' => $post->id
        ]);

        $response->assertCreated();
        $response->assertJsonFragment([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => 'Test comment',
        ]);
    }

    #[DataProvider('commentCreateProvider')]
    public function test_unauthenticated_user_cannot_create_comment($parent = null): void
    {
        $user = User::factory()->create();

        $post = Post::factory()->create();

        if (is_callable($parent))
        {
            $parent = $parent();
        }

        $response = $this->postJson('/api/comments', [
            'content' => 'Test comment',
            'user_id' => $user->id,
            'parent_id' => $parent ? $parent->id : null,
            'post_id' => $post->id
        ]);

        $response->assertUnauthorized();
        $this->assertDatabaseMissing('comments', [
            'content' => 'Test comment',
            'user_id' => $user->id,
            'parent_id' => $parent ? $parent->id : null,
            'post_id' => $post->id
        ]);
    }

    #[DataProvider('commentCreateProvider')]
    public function test_comment_cannot_be_created_with_invalid_input($parent = null): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $post = Post::factory()->create();

        if (is_callable($parent))
        {
            $parent = $parent();
        }

        $response = $this->postJson('/api/comments', [
            'content' => '',
            'user_id' => $user->id,
            'parent_id' => $parent ? $parent->id : null,
            'post_id' => $post->id
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseMissing('comments', [
            'content' => 'Test comment',
            'user_id' => $user->id,
            'parent_id' => $parent ? $parent->id : null,
            'post_id' => $post->id
        ]);
    }

    #[DataProvider('commentGeneralProvider')]
    public function test_authenticated_user_can_edit_own_comment($comment): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        if (is_callable($comment))
        {
            $comment = $comment($user->id);
        }

        $response = $this->patchJson('/api/comments/' . $comment->id, [
            'content' => 'Updated content'
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'content' => 'Updated content'
        ]);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content'
        ]);
    }

    #[DataProvider('commentGeneralProvider')]
    public function test_authenticated_user_cannot_edit_other_comment($comment): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        Sanctum::actingAs($user);

        if (is_callable($comment))
        {
            $comment = $comment($anotherUser->id);
        }

        $response = $this->patchJson('/api/comments/' . $comment->id, [
            'content' => 'Updated content'
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
            'content' => 'Updated content'
        ]);
    }

    #[DataProvider('commentGeneralProvider')]
    public function test_unauthenticated_user_cannot_edit_comment($comment): void
    {
        $user = User::factory()->create();
        if (is_callable($comment))
        {
            $comment = $comment($user->id);
        }

        $response = $this->patchJson('/api/comments/' . $comment->id, [
            'content' => 'Updated content'
        ]);

        $response->assertUnauthorized();
        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
            'content' => 'Updated content'
        ]);
    }

    #[DataProvider('commentGeneralProvider')]
    public function test_authenticated_user_can_delete_own_comment($comment): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $comment && $comment = $comment($user->id);

        $response = $this->deleteJson('/api/comments/' . $comment->id);
        $response->assertNoContent();
        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id
        ]);
    }

    #[DataProvider('commentGeneralProvider')]
    public function test_authenticated_user_cannot_delete_other_comment($comment): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        Sanctum::actingAs($user);

        $comment && $comment = $comment($anotherUser->id);

        $response = $this->deleteJson('/api/comments/' . $comment->id);
        $response->assertForbidden();
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id
        ]);
    }

    #[DataProvider('commentGeneralProvider')]
    public function test_unauthenticated_user_cannot_delete_comment($comment): void
    {
        $user = User::factory()->create();
        $comment && $comment = $comment($user->id);


        $response = $this->deleteJson('/api/comments/' . $comment->id);
        $response->assertUnauthorized();
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id
        ]);
    }
}
