<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can logout
     */
    public function test_authenticated_user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Logged out successfully');
    }

    /**
     * Test unauthenticated user cannot logout
     */
    public function test_unauthenticated_user_cannot_logout()
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }

    /**
     * Test token is invalidated after logout
     */
    public function test_token_is_invalidated_after_logout()
    {
        $user = User::factory()->create();

        // Login to get token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $token = $loginResponse->json('data.token');

        // Use token to access protected endpoint
        $meResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me');
        $meResponse->assertStatus(200);

        // Logout
        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout');
        $logoutResponse->assertStatus(200);

        // Try to use same token - should fail
        $meAfterLogout = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me');
        $meAfterLogout->assertStatus(401);
    }

    /**
     * Test logout response format
     */
    public function test_logout_response_format()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/logout');

        $response->assertJsonStructure([
            'message',
        ]);
    }
}
