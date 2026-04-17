<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful user login
     */
    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'token',
                ],
            ])
            ->assertJsonPath('data.user.email', 'john@example.com');
    }

    /**
     * Test login fails with incorrect password
     */
    public function test_login_fails_with_incorrect_password()
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Invalid credentials');
    }

    /**
     * Test login fails with non-existent email
     */
    public function test_login_fails_with_non_existent_email()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Invalid credentials');
    }

    /**
     * Test login fails with missing email
     */
    public function test_login_fails_with_missing_email()
    {
        $response = $this->postJson('/api/auth/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test login fails with missing password
     */
    public function test_login_fails_with_missing_password()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test login returns valid token
     */
    public function test_login_returns_valid_token()
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $token = $response->json('data.token');
        $this->assertNotNull($token);

        // Token should work with protected endpoints
        $me = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me');

        $me->assertStatus(200);
    }

    /**
     * Test login response includes user data
     */
    public function test_login_response_includes_correct_user_data()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertJsonPath('data.user.name', 'John Doe')
            ->assertJsonPath('data.user.email', 'john@example.com');
    }

    /**
     * Test multiple login attempts
     */
    public function test_multiple_login_attempts_all_succeed()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        // First login
        $response1 = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);
        $response1->assertStatus(200);

        // Second login should also work
        $response2 = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);
        $response2->assertStatus(200);

        // Tokens should be different
        $token1 = $response1->json('data.token');
        $token2 = $response2->json('data.token');
        $this->assertNotEquals($token1, $token2);
    }
}
