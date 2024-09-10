<?php

namespace Tests\Feature;

use App\Models\Drawer;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_retrieve_user_posts()
    {
        $user = User::factory()->create();


        $drawer = Drawer::factory()->create();
        Post::factory()->create(
            [
                'user_id' => $user->id,
                'type' => 'drawer',
                'drawer_id' => $drawer->id
            ]
        );

        $response = $this->getJson("/api/users/{$user->username}/posts");

        $response->assertOk();
        $response->assertJsonStructure([
            '*' => [
                'id', 'title', 'content', 'user', 'drawer', 'post_status', 'reacted_by_logged_user', 'favors_count', 'opposes_count', 'comments_count'
            ]
        ]);
    }
}
