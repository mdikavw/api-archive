<?php

namespace Tests\Feature;

use App\Models\Drawer;
use App\Models\DrawerUser;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_retrieve_posts()
    {
        $response = $this->getJson('/api/posts');

        $response->assertOk();
        $response->assertJsonStructure([
            '*' => [
                'id', 'title', 'content', 'slug', 'user', 'drawer', 'favors_count', 'opposes_count', 'comments_count'
            ]
        ]);
    }

    #[DataProvider('postProvider')]
    public function test_authenticated_user_can_create_post(string $type, $drawer = null)
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $drawer && $drawer = $drawer();

        $response = $this->postJson('/api/posts', [
            'title' => 'Test post',
            'user_id' => $user->id,
            'content' => 'Test content',
            'type' => $type,
            'drawer_id' => $drawer->id ?? null
        ]);

        $response->assertCreated();
        $response->assertJsonFragment([
            'title' => 'Test post',
            'content' => 'Test content'
        ]);
        $this->assertDatabaseHas('posts', [
            'title' => 'Test post',
            'user_id' => $user->id,
            'content' => 'Test content',
            'type' => $type === 'profile' ? 'profile' : 'drawer',
            'drawer_id' => $type === 'drawer' ? $drawer->id : null
        ]);
    }

    #[DataProvider('postProvider')]
    public function test_unauthenticated_user_cannot_create_post($type, $drawer = null)
    {
        $drawer && $drawer = $drawer();
        $response = $this->postJson('/api/posts', [
            'title' => 'Test post',
            'content' => 'Test content',
            'type' => $type,
            'drawer_id' => $drawer->id ?? null
        ]);

        $response->assertUnauthorized();
        $this->assertDatabaseMissing('posts', [
            'title' => 'Test post',
            'content' => 'Test content'
        ]);
    }

    #[DataProvider('invalidPostData')]
    public function test_post_creation_failed_with_invalid_data($title, $content, $type)
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/posts', [
            'title' => $title,
            'content' => $content,
            'type' => $type
        ]);

        $response->assertUnprocessable();
    }

    #[DataProvider('postProvider')]
    public function test_authenticated_user_can_edit_own_post($type, $drawer = null)
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $drawer && $drawer = $drawer();

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'type' => $type,
            'drawer_id' => $drawer->id ?? null
        ]);

        $response = $this->patchJson('/api/posts/' . $post->slug, [
            'title' => 'Updated title',
            'content' => 'Updated content'
        ]);
        $response->assertOk();
        $response->assertJsonFragment([
            'title' => 'Updated title',
            'content' => 'Updated content'
        ]);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated title',
            'content' => 'Updated content'
        ]);
    }

    #[DataProvider('postProvider')]
    public function test_authenticated_user_cannot_edit_other_post($type, $drawer = null)
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        Sanctum::actingAs($user);
        $drawer && $drawer = $drawer();

        $post = Post::factory()->create([
            'user_id' => $anotherUser->id,
            'type' => $type,
            'drawer_id' => $drawer->id ?? null
        ]);

        $response = $this->patchJson('/api/posts/' . $post->slug, [
            'title' => 'Updated title',
            'content' => 'Updated content'
        ]);
        $response->assertForbidden();
        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
            'title' => 'Updated title',
            'content' => 'Updated content'
        ]);
    }

    #[DataProvider('postProvider')]
    public function test_unauthenticated_user_cannot_edit_post($type, $drawer = null)
    {
        $drawer && $drawer = $drawer();

        $post = Post::factory()->create([
            'title' => 'Test title',
            'content' => 'Test content',
            'type' => $type,
            'drawer_id' => $drawer->id ?? null
        ]);

        $response = $this->patchJson('/api/posts/' . $post->slug, [
            'title' => 'Updated title',
            'content' => 'Updated content'
        ]);
        $response->assertUnauthorized();
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Test title',
            'content' => 'Test content'
        ]);
    }

    #[DataProvider('postProvider')]
    public function test_authenticated_user_can_delete_own_post($type, $drawer = null)
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $drawer && $drawer = $drawer();

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'type' => $type,
            'drawer_id' => $drawer->id ?? null
        ]);

        $response = $this->deleteJson('/api/posts/' . $post->slug);
        $response->assertSuccessful();
        $this->assertDatabaseMissing('posts', [
            'id' => $post->id
        ]);
    }

    #[DataProvider('postProvider')]
    public function test_unauthenticated_user_cannot_delete_own_post($type, $drawer = null)
    {
        $user = User::factory()->create();

        $drawer && $drawer = $drawer();

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'type' => $type,
            'drawer_id' => $drawer->id ?? null
        ]);

        $response = $this->deleteJson('/api/posts/' . $post->slug);
        $response->assertUnauthorized();
        $this->assertDatabaseHas('posts', [
            'id' => $post->id
        ]);
    }

    #[DataProvider('postProvider')]
    public function test_authenticated_user_cannot_delete_edit_other_post($type, $drawer = null)
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        Sanctum::actingAs($user);
        $drawer && $drawer = $drawer();

        $post = Post::factory()->create([
            'user_id' => $anotherUser->id,
            'type' => $type,
            'drawer_id' => $drawer->id ?? null
        ]);

        $response = $this->deleteJson('/api/posts/' . $post->slug);
        $response->assertForbidden();
        $this->assertDatabaseHas('posts', [
            'id' => $post->id
        ]);
    }

    #[DataProvider('statusChangeProvider')]
    public function test_moderator_can_change_post_status($status)
    {
        $user = User::factory()->create();
        $drawer = Drawer::factory()->create();
        $statusMap = [
            'approve' => 1,
            'reject' => 2
        ];

        Sanctum::actingAs($user);

        DrawerUser::create([
            'drawer_id' => $drawer->id,
            'user_id' => $user->id,
            'role_id' => 2
        ]);

        $post = Post::factory()->create([
            'type' => 'drawer',
            'drawer_id' => $drawer->id,
        ]);

        $response = $this->patchJson("/api/posts/{$post->slug}/status", [
            'status' => $status
        ]);
        $response->assertSuccessful();
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'post_status_id' => $statusMap[$status]
        ]);
    }

    #[DataProvider('statusChangeProvider')]
    public function test_non_moderator_cannot_change_post_status($status)
    {
        $user = User::factory()->create();
        $drawer = Drawer::factory()->create();
        $statusMap = [
            'approve' => 1,
            'reject' => 2
        ];

        Sanctum::actingAs($user);

        DrawerUser::create([
            'drawer_id' => $drawer->id,
            'user_id' => $user->id,
            'role_id' => 3
        ]);

        $post = Post::factory()->create([
            'type' => 'drawer',
            'drawer_id' => $drawer->id,
        ]);

        $response = $this->patchJson("/api/posts/{$post->slug}/status", [
            'status' => $status
        ]);
        $response->assertForbidden();
        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
            'post_status_id' => $statusMap[$status]
        ]);
    }

    public static function postProvider()
    {
        return [
            'profile post' => ['profile', null],
            'drawer post' => ['drawer', fn () => Drawer::factory()->create()]
        ];
    }

    public static function invalidPostData()
    {
        return [
            ['', 'Test content', 'profile'],
            ['Test title', '', 'drawer']
        ];
    }

    public static function statusChangeProvider()
    {
        return [
            'approve' => ['approve'],
            'reject' => ['reject']
        ];
    }
}
