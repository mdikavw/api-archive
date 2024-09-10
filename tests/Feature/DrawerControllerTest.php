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

class DrawerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public static function drawerUserRoleProvider(): array
    {
        return [
            'role user' => ['user'],
            'role moderator' => ['moderator']
        ];
    }

    public function test_can_create_drawer()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/drawers', [
            'name' => 'Test drawer',
            'description' => 'Test description'
        ]);

        $response->assertCreated();
        $response->assertJsonFragment([
            'name' => 'Test drawer',
            'description' => 'Test description'
        ]);
        $this->assertDatabaseHas('drawers', [
            'name' => 'Test drawer',
            'description' => 'Test description'
        ]);
    }

    public function test_drawers_can_be_retrieved()
    {
        Drawer::factory(5)->create();

        $response = $this->getJson('/api/drawers');
        $response->assertSuccessful();
        $response->assertJsonStructure([
            '*' => [
                'id', 'name', 'description'
            ]
        ]);
    }

    public function test_drawer_can_be_viewed()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $drawer = Drawer::factory()->create();

        $response = $this->getJson("/api/drawers/{$drawer->name}");
        $response->assertSuccessful();
        $response->assertJsonFragment(
            [
                'id' => $drawer->id,
                'name' => $drawer->name
            ]
        );
    }

    #[DataProvider('drawerUserRoleProvider')]
    public function test_get_role_in_drawer($role)
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $roleMap = [
            'moderator' => 2,
            'user' => 3
        ];

        $drawer = Drawer::factory()->create();
        DrawerUser::create([
            'user_id' => $user->id,
            'drawer_id' => $drawer->id,
            'role_id' => $roleMap[$role]
        ]);

        $response = $this->getJson("/api/drawers/{$drawer->name}");
        $response->assertSuccessful();
        $response->assertJsonFragment(['role' => $role]);
    }

    public function test_can_retrieve_post_in_drawer()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $drawer = Drawer::factory()->create();

        Post::factory(5)->create([
            'drawer_id' => $drawer->id
        ]);

        $response = $this->getJson("/api/drawers/{$drawer->name}/posts");
        $response->assertSuccessful();
        $response->assertJsonStructure(
            ['*' => [
                'id', 'title', 'content', 'user', 'drawer', 'post_status', 'reacted_by_logged_user', 'favors_count', 'opposes_count', 'comments_count'
            ]]
        );
    }

    public function test_moderator_can_update_drawer()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $drawer = Drawer::factory()->create(
            [
                'name' => 'Test drawer',
                'description' => 'Test description'
            ]
        );
        DrawerUser::create([
            'user_id' => $user->id,
            'drawer_id' => $drawer->id,
            'role_id' => 2
        ]);

        $response = $this->patchJson('/api/drawers/' . $drawer->name, [
            'name' => 'Updated drawer',
            'description' => 'Updated description'
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => 'Updated drawer',
            'description' => 'Updated description'
        ]);
        $this->assertDatabaseHas('drawers', [
            'id' => $drawer->id,
            'name' => 'Updated drawer',
            'description' => 'Updated description'
        ]);
    }

    public function test_non_moderator_cannot_update_drawer()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $drawer = Drawer::factory()->create(
            [
                'name' => 'Test drawer',
                'description' => 'Test description'
            ]
        );
        DrawerUser::create([
            'user_id' => $user->id,
            'drawer_id' => $drawer->id,
            'role_id' => 3
        ]);

        $response = $this->patchJson('/api/drawers/' . $drawer->name, [
            'name' => 'Updated drawer',
            'description' => 'Updated description'
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('drawers', [
            'id' => $drawer->id,
            'name' => 'Updated drawer',
            'description' => 'Updated description'
        ]);
    }

    public function test_moderator_can_delete_drawer()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $drawer = Drawer::factory()->create();
        DrawerUser::create(
            [
                'user_id' => $user->id,
                'drawer_id' => $drawer->id,
                'role_id' => 2
            ]
        );

        $response = $this->deleteJson('/api/drawers/' . $drawer->name);
        $response->assertNoContent();
        $this->assertDatabaseMissing('drawers', [
            'id' => $drawer->id
        ]);
    }

    public function test_non_moderator_cannot_delete_drawer()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $drawer = Drawer::factory()->create();
        DrawerUser::create(
            [
                'user_id' => $user->id,
                'drawer_id' => $drawer->id,
                'role_id' => 3
            ]
        );

        $response = $this->deleteJson('/api/drawers/' . $drawer->name);
        $response->assertForbidden();
        $this->assertDatabaseHas('drawers', [
            'id' => $drawer->id
        ]);
    }
}
