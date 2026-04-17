<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful user registration
     */
    public function test_user_can_register_with_valid_data()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('user.name', 'John Doe')
            ->assertJsonPath('user.email', 'john@example.com');

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);

        $this->assertTrue(User::count() === 1);
    }

    /**
     * Test registration with invalid email
     */
    public function test_registration_fails_with_invalid_email()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertTrue(User::count() === 0);
    }

    /**
     * Test registration with duplicate email
     */
    public function test_registration_fails_with_duplicate_email()
    {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertTrue(User::count() === 1);
    }

    /**
     * Test registration with short password
     */
    public function test_registration_fails_with_short_password()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test registration with mismatched password confirmation
     */
    public function test_registration_fails_with_mismatched_password_confirmation()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test registration with missing required fields
     */
    public function test_registration_fails_with_missing_fields()
    {
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * Test registered user receives token
     */
    public function test_registered_user_receives_valid_token()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertNotNull($response->json('data.token'));

        // Token should be usable in protected endpoints
        $token = $response->json('data.token');
        $me = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me');

        $me->assertStatus(200)
            ->assertJsonPath('data.email', 'john@example.com');
    }

    /**
     * Test registration requires POST method
     */
    public function test_registration_requires_post_method()
    {
        $response = $this->getJson('/api/auth/register');
        $response->assertStatus(405); // Method Not Allowed
    }

    /**
     * Test response includes proper headers
     */
    public function test_registration_response_has_correct_headers()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertHeader('Content-Type', 'application/json');
    }
}
