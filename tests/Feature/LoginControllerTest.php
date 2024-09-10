<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_user_can_login()
    {
        User::factory()->create([
            'username' => 'testusername',
            'password' => bcrypt('testpassword')
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'testusername',
            'password' => 'testpassword'
        ]);

        $response->assertOk();
    }

    public function test_login_with_empty_username_returns_unprocessable()
    {
        User::factory()->create([
            'username' => 'testusername',
            'password' => bcrypt('testpassword')
        ]);

        $response = $this->postJson('/api/login', [
            'username' => '',
            'password' => 'testpassword'
        ]);

        $response->assertStatus(422); // Unprocessable Entity
    }

    public function test_login_with_username_length_less_than_3_returns_unprocessable()
    {
        $response = $this->postJson('/api/login', [
            'username' => 'tu',
            'password' => 'testpassword'
        ]);

        $response->assertStatus(422); // Unprocessable Entity
    }

    public function test_login_with_username_length_more_than_24_returns_unprocessable()
    {
        $response = $this->postJson('/api/login', [
            'username' => 'testusernametestusernameuser',
            'password' => 'testpassword'
        ]);

        $response->assertStatus(422); // Unprocessable Entity
    }

    public function test_login_with_empty_password_returns_unprocessable()
    {
        $response = $this->postJson('/api/login', [
            'username' => 'testusername',
            'password' => ''
        ]);

        $response->assertStatus(422); // Unprocessable Entity
    }

    public function test_login_with_password_length_less_than_8_returns_unprocessable()
    {
        $response = $this->postJson('/api/login', [
            'username' => 'testusername',
            'password' => 'pass'
        ]);

        $response->assertStatus(422); // Unprocessable Entity
    }

    public function test_user_cannot_login_with_wrong_password()
    {
        User::factory()->create([
            'username' => 'testusername',
            'password' => bcrypt('testpassword')
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'testusername',
            'password' => 'wrongpassword'
        ]);

        $response->assertUnauthorized();
        $response->assertJsonFragment([
            'message' => 'Wrong username or password'
        ]);
    }

    public function test_user_cannot_login_with_nonexistent_account()
    {
        $response = $this->postJson('/api/login', [
            'username' => 'nonexistentusername',
            'password' => 'nonexistentpassword'
        ]);

        $response->assertStatus(422); // Unprocessable Entity
    }
}
