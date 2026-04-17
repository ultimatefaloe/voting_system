<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use App\Models\Election;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ElectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organization $org;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->org = Organization::factory()->create();
        $this->user->organizations()->attach($this->org, ['role' => 'admin']);
    }

    /**
     * Test user can create election in their organization
     */
    public function test_user_can_create_election()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/organizations/{$this->org->id}/elections", [
                'title' => 'Board Election 2025',
                'description' => 'Annual board election',
                'type' => 'public',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'type',
                    'status',
                    'created_at',
                ],
            ])
            ->assertJsonPath('data.title', 'Board Election 2025')
            ->assertJsonPath('data.status', 'draft');

        $this->assertDatabaseHas('elections', [
            'title' => 'Board Election 2025',
        ]);
    }

    /**
     * Test election creation requires title
     */
    public function test_election_creation_requires_title()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/organizations/{$this->org->id}/elections", [
                'description' => 'An election',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /**
     * Test user can list organization elections
     */
    public function test_user_can_list_organization_elections()
    {
        Election::factory()->count(3)->create(['organization_id' => $this->org->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/organizations/{$this->org->id}/elections");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'status',
                    ],
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test user can retrieve election details
     */
    public function test_user_can_retrieve_election_details()
    {
        $election = Election::factory()->create(['organization_id' => $this->org->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/elections/{$election->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.title', $election->title);
    }

    /**
     * Test user can publish election
     */
    public function test_user_can_publish_election()
    {
        $election = Election::factory()
            ->state(['status' => 'draft'])
            ->create(['organization_id' => $this->org->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/elections/{$election->id}/publish");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'published');

        $this->assertDatabaseHas('elections', [
            'id' => $election->id,
            'status' => 'published',
        ]);
    }

    /**
     * Test user cannot publish published election
     */
    public function test_user_cannot_publish_published_election()
    {
        $election = Election::factory()
            ->state(['status' => 'published'])
            ->create(['organization_id' => $this->org->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/elections/{$election->id}/publish");

        $response->assertStatus(422);
    }

    /**
     * Test user can close election
     */
    public function test_user_can_close_election()
    {
        $election = Election::factory()
            ->state(['status' => 'published'])
            ->create(['organization_id' => $this->org->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/elections/{$election->id}/close");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'closed');

        $this->assertDatabaseHas('elections', [
            'id' => $election->id,
            'status' => 'closed',
        ]);
    }

    /**
     * Test member cannot publish election
     */
    public function test_member_cannot_publish_election()
    {
        $member = User::factory()->create();
        $this->org->members()->attach($member, ['role' => 'member']);

        $election = Election::factory()
            ->state(['status' => 'draft'])
            ->create(['organization_id' => $this->org->id]);

        $response = $this->actingAs($member, 'sanctum')
            ->postJson("/api/elections/{$election->id}/publish");

        $response->assertStatus(403);
    }

    /**
     * Test user can delete draft election
     */
    public function test_user_can_delete_draft_election()
    {
        $election = Election::factory()
            ->state(['status' => 'draft'])
            ->create(['organization_id' => $this->org->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/elections/{$election->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('elections', ['id' => $election->id]);
    }

    /**
     * Test user cannot delete published election
     */
    public function test_user_cannot_delete_published_election()
    {
        $election = Election::factory()
            ->state(['status' => 'published'])
            ->create(['organization_id' => $this->org->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/elections/{$election->id}");

        $response->assertStatus(422);
    }
}
