<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReactionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    const REACTABLE_TYPE_MAP = [
        'post' => Post::class,
        'comment' => Comment::class
    ];

    public static function reactionCreateProvider()
    {
        return [
            'favor post' => ['favor', fn () => Post::factory()->create(), 'post'],
            'oppose post' => ['oppose', fn () => Post::factory()->create(), 'post'],
            'favor comment' => ['favor', fn () => Comment::factory()->create(), 'comment'],
            'oppose comment' => ['oppose', fn () => Comment::factory()->create(), 'comment']
        ];
    }

    public static function reactionUpdateProvider()
    {
        return [
            'favor to oppose post' => ['favor', 'oppose', fn () => Post::factory()->create(), 'post'],
            'oppose to favor post' => ['oppose', 'favor', fn () => Post::factory()->create(), 'post'],
            'favor to oppose comment' => ['favor', 'oppose', fn () => Comment::factory()->create(), 'comment'],
            'oppose to favor comment' => ['oppose', 'favor', fn () => Comment::factory()->create(), 'comment'],
        ];
    }

    public static function reactionDeleteProvider()
    {
        return [
            'delete post' => [fn () => Post::factory()->create(), 'post'],
            'delete comment' => [fn () => Comment::factory()->create(), 'comment'],
        ];
    }

    public static function reactionGeneralProvider()
    {
        return [
            'reactable post' => ['post', fn () => Post::factory()],
            'reactable comment' => ['comment', fn () => Comment::factory()],
        ];
    }

    #[DataProvider('reactionCreateProvider')]
    public function test_authenticated_user_can_react($type, $reactable, $reactableType)
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $reactable = $reactable();

        $response = $this->postJson('/api/reactions', [
            'type' => $type,
            'reactable_id' => $reactable->id,
            'reactable_type' => $reactableType
        ]);

        $response->assertCreated();
        $response->assertJsonFragment([
            'type' => $type,
            'reactable_id' => $reactable->id,
            'reactable_type' => $reactableType
        ]);

        $this->assertDatabaseHas('reactions', [
            'type' => $type,
            'user_id' => $user->id,
            'reactable_id' => $reactable->id,
            'reactable_type' => self::REACTABLE_TYPE_MAP[$reactableType]
        ]);
    }

    #[DataProvider('reactionCreateProvider')]
    public function test_unauthenticated_user_cannot_react($type, $reactable, $reactableType)
    {
        $user = User::factory()->create();

        $reactable = $reactable();

        $response = $this->postJson('/api/reactions', [
            'type' => $type,
            'reactable_id' => $reactable->id,
            'reactable_type' => $reactableType
        ]);

        $response->assertUnauthorized();

        $this->assertDatabaseMissing('reactions', [
            'type' => $type,
            'user_id' => $user->id,
            'reactable_id' => $reactable->id,
            'reactable_type' => self::REACTABLE_TYPE_MAP[$reactableType]
        ]);
    }

    #[DataProvider('reactionUpdateProvider')]
    public function test_authenticated_user_can_change_reaction($oldType, $type, $reactable, $reactableType)
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $reactable = $reactable();

        $reaction = Reaction::factory()->create([
            'user_id' => $user->id,
            'reactable_id' => $reactable->id,
            'reactable_type' => self::REACTABLE_TYPE_MAP[$reactableType],
            'type' => $oldType
        ]);

        $response = $this->patchJson('/api/reactions/' . $reaction->id, [
            'type' => $type
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'type' => $type,
            'reactable_id' => $reactable->id,
            'reactable_type' => $reactableType
        ]);

        $this->assertDatabaseHas('reactions', [
            'user_id' => $user->id,
            'reactable_id' => $reactable->id,
            'reactable_type' => self::REACTABLE_TYPE_MAP[$reactableType],
            'type' => $type
        ]);
    }

    #[DataProvider('reactionUpdateProvider')]
    public function test_unauthorized_user_cannot_change_reaction($oldType, $type, $reactable, $reactableType)
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        Sanctum::actingAs($user);

        $reactable = $reactable();

        $reaction = Reaction::factory()->create([
            'user_id' => $anotherUser->id,
            'reactable_id' => $reactable->id,
            'reactable_type' => self::REACTABLE_TYPE_MAP[$reactableType],
            'type' => $oldType
        ]);

        $response = $this->patchJson('/api/reactions/' . $reaction->id, [
            'type' => $type
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('reactions', [
            'user_id' => $anotherUser->id,
            'reactable_id' => $reactable->id,
            'reactable_type' => self::REACTABLE_TYPE_MAP[$reactableType],
            'type' => $type
        ]);
    }

    #[DataProvider('reactionUpdateProvider')]
    public function test_unuthenticated_user_cannot_change_reaction($oldType, $type, $reactable, $reactableType)
    {
        $user = User::factory()->create();

        $reactable = $reactable();

        $reaction = Reaction::factory()->create([
            'user_id' => $user->id,
            'reactable_id' => $reactable->id,
            'reactable_type' => self::REACTABLE_TYPE_MAP[$reactableType],
            'type' => $oldType
        ]);

        $response = $this->patchJson('/api/reactions/' . $reaction->id, [
            'type' => $type
        ]);

        $response->assertUnauthorized();

        $this->assertDatabaseMissing('reactions', [
            'user_id' => $user->id,
            'reactable_id' => $reactable->id,
            'reactable_type' => self::REACTABLE_TYPE_MAP[$reactableType],
            'type' => $type
        ]);
    }

    #[DataProvider('reactionDeleteProvider')]
    public function test_authenticated_user_can_delete_reaction($reactable, $reactableType)
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $reactable = $reactable();

        $reaction = Reaction::factory()->create([
            'user_id' => $user->id,
            'reactable_id' => $reactable->id,
            'reactable_type' => $reactableType
        ]);

        $response = $this->deleteJson("/api/reactions/{$reaction->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('reactions', [
            'id' => $reaction->id
        ]);
    }

    #[DataProvider('reactionDeleteProvider')]
    public function test_unauthorized_user_cannot_delete_reaction($reactable, $reactableType)
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        Sanctum::actingAs($user);

        $reactable = $reactable();

        $reaction = Reaction::factory()->create([
            'user_id' => $anotherUser->id,
            'reactable_id' => $reactable->id,
            'reactable_type' => $reactableType
        ]);

        $response = $this->deleteJson("/api/reactions/{$reaction->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('reactions', [
            'id' => $reaction->id
        ]);
    }

    #[DataProvider('reactionDeleteProvider')]
    public function test_unauthenticated_user_cannot_delete_reaction($reactable, $reactableType)
    {
        $user = User::factory()->create();

        $reactable = $reactable();

        $reaction = Reaction::factory()->create([
            'user_id' => $user->id,
            'reactable_id' => $reactable->id,
            'reactable_type' => $reactableType
        ]);

        $response = $this->deleteJson("/api/reactions/{$reaction->id}");

        $response->assertUnauthorized();

        $this->assertDatabaseHas('reactions', [
            'id' => $reaction->id
        ]);
    }

    #[DataProvider('reactionGeneralProvider')]
    public function test_react_to_nonexistent_entity_returns_error($reactableType, $reactable)
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $nonExistentEntityId = 9999;
        $reactable = $reactable()->create([
            'id' => $nonExistentEntityId
        ]);
        $reactable->delete();

        $response = $this->postJson('/api/reactions', [
            'reactable_id' => $nonExistentEntityId,
            'reactable_type' => $reactableType,
            'type' => fake()->randomElement(['favor', 'oppose'])
        ]);

        $response->assertNotFound();
    }

    #[DataProvider('reactionGeneralProvider')]
    public function test_invalid_reaction_type_returns_error($reactableType, $reactable)
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $reactable = $reactable()->create();
        $response = $this->postJson('/api/reactions', [
            'reactable_id' => $reactable->id,
            'reactable_type' => self::REACTABLE_TYPE_MAP[$reactableType],
            'type' => 'other'
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['type']);
    }
}
