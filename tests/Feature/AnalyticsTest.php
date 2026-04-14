<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use App\Models\Election;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
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
     * Test user can view organization analytics
     */
    public function test_user_can_view_organization_analytics()
    {
        Election::factory()->count(3)->create(['organization_id' => $this->org->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/organizations/{$this->org->id}/analytics");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_elections',
                    'active_elections',
                    'closed_elections',
                    'total_votes',
                    'average_turnout',
                ],
            ]);
    }

    /**
     * Test unauthenticated user cannot view analytics
     */
    public function test_unauthenticated_user_cannot_view_analytics()
    {
        $response = $this->getJson("/api/organizations/{$this->org->id}/analytics");

        $response->assertStatus(401);
    }

    /**
     * Test user cannot view analytics for organization they're not member of
     */
    public function test_user_cannot_view_analytics_for_organization_they_are_not_member_of()
    {
        $otherOrg = Organization::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/organizations/{$otherOrg->id}/analytics");

        $response->assertStatus(403);
    }

    /**
     * Test analytics trends
     */
    public function test_analytics_trends()
    {
        Election::factory()->count(5)->create(['organization_id' => $this->org->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/organizations/{$this->org->id}/analytics/trends");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'period',
                        'elections',
                        'votes',
                    ],
                ],
            ]);
    }

    /**
     * Test competitive elections analytics
     */
    public function test_competitive_elections_analytics()
    {
        Election::factory()->count(3)->create(['organization_id' => $this->org->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/organizations/{$this->org->id}/analytics/competitive");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'election_id',
                        'title',
                        'competitiveness_score',
                    ],
                ],
            ]);
    }

    /**
     * Test turnout analytics
     */
    public function test_turnout_analytics()
    {
        Election::factory()->count(3)->create(['organization_id' => $this->org->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/organizations/{$this->org->id}/analytics/turnout");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'election_id',
                        'title',
                        'turnout_percentage',
                        'total_votes',
                    ],
                ],
            ]);
    }

    /**
     * Test growth analytics
     */
    public function test_growth_analytics()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/organizations/{$this->org->id}/analytics/growth");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'elections_growth',
                    'voting_growth',
                    'member_growth',
                ],
            ]);
    }

    /**
     * Test timeline analytics
     */
    public function test_timeline_analytics()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/organizations/{$this->org->id}/analytics/timeline");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'date',
                        'elections',
                        'votes',
                    ],
                ],
            ]);
    }

    /**
     * Test analytics requires GET method
     */
    public function test_analytics_requires_get_method()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/organizations/{$this->org->id}/analytics");

        $response->assertStatus(405);
    }
}
