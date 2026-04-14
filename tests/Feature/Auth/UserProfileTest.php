<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can view their profile
     */
    public function test_authenticated_user_can_view_their_profile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.name', $user->name);
    }

    /**
     * Test unauthenticated user cannot view profile
     */
    public function test_unauthenticated_user_cannot_view_profile()
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    /**
     * Test user with invalid token cannot view profile
     */
    public function test_user_with_invalid_token_cannot_view_profile()
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    /**
     * Test profile returns correct user data
     */
    public function test_profile_returns_correct_user_data()
    {
        $user = User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/auth/me');

        $response->assertJsonPath('data.name', 'Jane Smith')
            ->assertJsonPath('data.email', 'jane@example.com');
    }

    /**
     * Test profile endpoint requires GET method
     */
    public function test_profile_endpoint_requires_get_method()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/me');

        $response->assertStatus(405); // Method Not Allowed
    }
}
