<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can create organization
     */
    public function test_user_can_create_organization()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/organizations', [
                'name' => 'Test Organization',
                'description' => 'A test organization',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.name', 'Test Organization');

        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Organization',
        ]);
    }

    /**
     * Test unauthenticated user cannot create organization
     */
    public function test_unauthenticated_user_cannot_create_organization()
    {
        $response = $this->postJson('/api/organizations', [
            'name' => 'Test Organization',
            'description' => 'A test organization',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test organization creation requires name
     */
    public function test_organization_creation_requires_name()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/organizations', [
                'description' => 'A test organization',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test user can list their organizations
     */
    public function test_user_can_list_their_organizations()
    {
        $user = User::factory()->create();
        Organization::factory()->count(3)->create();
        $userOrg = Organization::factory()->create();
        $user->organizations()->attach($userOrg);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/organizations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                    ],
                ],
            ]);

        // Should only return organizations user is member of
        $this->assertCount(1, $response->json('data'));
    }

    /**
     * Test user can retrieve organization details
     */
    public function test_user_can_retrieve_organization_details()
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $user->organizations()->attach($org);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/organizations/{$org->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name', $org->name);
    }

    /**
     * Test user cannot access organization they're not member of
     */
    public function test_user_cannot_access_organization_they_are_not_member_of()
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/organizations/{$org->id}");

        $response->assertStatus(403);
    }

    /**
     * Test user can update organization they own
     */
    public function test_user_can_update_organization_they_own()
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $user->organizations()->attach($org, ['role' => 'admin']);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/organizations/{$org->id}", [
                'name' => 'Updated Name',
                'description' => 'Updated description',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('organizations', [
            'id' => $org->id,
            'name' => 'Updated Name',
        ]);
    }

    /**
     * Test member cannot update organization
     */
    public function test_member_cannot_update_organization()
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $user->organizations()->attach($org, ['role' => 'member']);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/organizations/{$org->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test user can delete organization they own
     */
    public function test_user_can_delete_organization_they_own()
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $user->organizations()->attach($org, ['role' => 'admin']);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/organizations/{$org->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('organizations', ['id' => $org->id]);
    }

    /**
     * Test retrieving non-existent organization returns 404
     */
    public function test_retrieving_non_existent_organization_returns_404()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/organizations/nonexistent-id');

        $response->assertStatus(404);
    }
}
