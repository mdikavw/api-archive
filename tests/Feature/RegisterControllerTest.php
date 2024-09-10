<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public static function registerInvalidInputProvider()
    {
        return [
            'empty username' => ['', 'testuser@email.com', 'testpassword', 'testpassword'],
            'lower than min username' => ['tu', 'testuser@email.com', 'testpassword', 'testpassword'],
            'larger than max username' => ['testusernametestusernametest', 'testuser@email.com', 'testpassword', 'testpassword'],
            'empty email' => ['testusername', '', 'testpassword', 'testpassword'],
            'invalid email' => ['testusername', 'testuser@', 'testpassword', 'testpassword'],
            'empty password' => ['testusername', 'testuser@', '', 'testpassword'],
            'lower than min username' => ['testusername', 'testuser@', 'test', 'test'],
            'password confirmation not match' => ['testusername', 'testuser@', 'testpassword', 'differentpassword'],
        ];
    }


    public function test_user_can_register()
    {
        $response = $this->postJson('/api/register', [
            'username' => 'testuser',
            'email' => 'testuser@email.com',
            'password' => 'testpassword',
            'password_confirmation' => 'testpassword'
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas(
            'users',
            [
                'username' => 'testuser',
                'email' => 'testuser@email.com',
            ]
        );
    }

    #[DataProvider('registerInvalidInputProvider')]
    public function test_cannot_register_with_invalid_input($username, $email, $password, $passwordConfirmation)
    {
        $response = $this->postJson('/api/register', [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseMissing(
            'users',
            [
                'username' => $username,
                'email' => $email,
            ]
        );
    }

    public function test_cannot_register_with_duplicate_username()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/register', [
            'username' => $user->username,
            'email' => 'test@email.com',
            'password' => 'testpassword',
            'password_confirmation' => 'testpassword'
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseMissing('users', [
            'username' => $user->username,
            'email' => 'test@email.com'
        ]);
    }
}
