<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Election;
use App\Models\Position;
use App\Models\Candidate;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultsTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $org;
    protected Election $election;
    protected Position $position;
    protected Candidate $candidate1;
    protected Candidate $candidate2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::factory()->create();
        $this->election = Election::factory()
            ->state(['status' => 'closed'])
            ->create(['organization_id' => $this->org->id]);

        $this->position = Position::factory()->create(['election_id' => $this->election->id]);

        $this->candidate1 = Candidate::factory()->create(['position_id' => $this->position->id]);
        $this->candidate2 = Candidate::factory()->create(['position_id' => $this->position->id]);

        // Create some votes
        Vote::factory()->count(5)->create([
            'election_id' => $this->election->id,
            'candidate_id' => $this->candidate1->id,
        ]);

        Vote::factory()->count(3)->create([
            'election_id' => $this->election->id,
            'candidate_id' => $this->candidate2->id,
        ]);
    }

    /**
     * Test anyone can view election results
     */
    public function test_anyone_can_view_election_results()
    {
        $response = $this->getJson("/api/elections/{$this->election->id}/results");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'position_id',
                        'candidates' => [
                            '*' => [
                                'id',
                                'name',
                                'votes',
                            ],
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Test results show correct vote counts
     */
    public function test_results_show_correct_vote_counts()
    {
        $response = $this->getJson("/api/elections/{$this->election->id}/results");

        $candidates = $response->json('data.0.candidates');

        $candidate1Results = collect($candidates)->firstWhere('id', $this->candidate1->id);
        $candidate2Results = collect($candidates)->firstWhere('id', $this->candidate2->id);

        $this->assertEquals(5, $candidate1Results['votes']);
        $this->assertEquals(3, $candidate2Results['votes']);
    }

    /**
     * Test results by position
     */
    public function test_results_by_position()
    {
        $response = $this->getJson("/api/elections/{$this->election->id}/results/by-position");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'position_id',
                        'total_votes',
                    ],
                ],
            ]);
    }

    /**
     * Test results by candidate
     */
    public function test_results_by_candidate()
    {
        $response = $this->getJson("/api/elections/{$this->election->id}/results/by-candidate");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'candidate_id',
                        'position_id',
                        'votes',
                    ],
                ],
            ]);
    }

    /**
     * Test results statistics
     */
    public function test_results_statistics()
    {
        $response = $this->getJson("/api/elections/{$this->election->id}/results/statistics");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_votes',
                    'positions',
                    'candidates',
                ],
            ]);

        $this->assertEquals(8, $response->json('data.total_votes'));
    }

    /**
     * Test results summary
     */
    public function test_results_summary()
    {
        $response = $this->getJson("/api/elections/{$this->election->id}/results/summary");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'position_id',
                        'winner_id',
                        'winner_name',
                    ],
                ],
            ]);
    }

    /**
     * Test cannot view results for draft election
     */
    public function test_cannot_view_results_for_draft_election()
    {
        $draftElection = Election::factory()
            ->state(['status' => 'draft'])
            ->create(['organization_id' => $this->org->id]);

        $response = $this->getJson("/api/elections/{$draftElection->id}/results");

        $response->assertStatus(403);
    }

    /**
     * Test results endpoint requires GET method
     */
    public function test_results_endpoint_requires_get_method()
    {
        $response = $this->postJson("/api/elections/{$this->election->id}/results");

        $response->assertStatus(405);
    }
}
