<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use App\Models\Election;
use App\Models\Position;
use App\Models\Candidate;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VotingTest extends TestCase
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
            ->state(['status' => 'published'])
            ->create(['organization_id' => $this->org->id]);

        $this->position = Position::factory()->create(['election_id' => $this->election->id]);

        $this->candidate1 = Candidate::factory()->create(['position_id' => $this->position->id]);
        $this->candidate2 = Candidate::factory()->create(['position_id' => $this->position->id]);
    }

    /**
     * Test voter can get ballot
     */
    public function test_voter_can_get_ballot()
    {
        $voterToken = 'test-voter-token-' . $this->election->id;

        $response = $this->withHeader('X-Voter-Token', $voterToken)
            ->getJson("/api/elections/{$this->election->id}/ballot");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'candidates' => [
                            '*' => [
                                'id',
                                'name',
                            ],
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Test voter can submit vote
     */
    public function test_voter_can_submit_vote()
    {
        $voterToken = 'test-voter-token-' . $this->election->id;

        $response = $this->withHeader('X-Voter-Token', $voterToken)
            ->postJson("/api/elections/{$this->election->id}/vote", [
                'candidate_id' => $this->candidate1->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'position_id',
                    'candidate_id',
                ],
            ]);

        $this->assertDatabaseHas('votes', [
            'candidate_id' => $this->candidate1->id,
            'election_id' => $this->election->id,
        ]);
    }

    /**
     * Test voter cannot vote twice for same position
     */
    public function test_voter_cannot_vote_twice_for_same_position()
    {
        $voterToken = 'test-voter-token-' . $this->election->id;

        // First vote
        $this->withHeader('X-Voter-Token', $voterToken)
            ->postJson("/api/elections/{$this->election->id}/vote", [
                'candidate_id' => $this->candidate1->id,
            ]);

        // Second vote for same position
        $response = $this->withHeader('X-Voter-Token', $voterToken)
            ->postJson("/api/elections/{$this->election->id}/vote", [
                'candidate_id' => $this->candidate2->id,
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test voter cannot vote for non-existent candidate
     */
    public function test_voter_cannot_vote_for_non_existent_candidate()
    {
        $voterToken = 'test-voter-token-' . $this->election->id;

        $response = $this->withHeader('X-Voter-Token', $voterToken)
            ->postJson("/api/elections/{$this->election->id}/vote", [
                'candidate_id' => 'non-existent-id',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test voter can submit batch votes
     */
    public function test_voter_can_submit_batch_votes()
    {
        $position2 = Position::factory()->create(['election_id' => $this->election->id]);
        $candidate3 = Candidate::factory()->create(['position_id' => $position2->id]);

        $voterToken = 'test-voter-token-' . $this->election->id;

        $response = $this->withHeader('X-Voter-Token', $voterToken)
            ->postJson("/api/elections/{$this->election->id}/votes", [
                'votes' => [
                    ['position_id' => $this->position->id, 'candidate_id' => $this->candidate1->id],
                    ['position_id' => $position2->id, 'candidate_id' => $candidate3->id],
                ],
            ]);

        $response->assertStatus(201);

        $this->assertEquals(2, Vote::where('election_id', $this->election->id)->count());
    }

    /**
     * Test voter cannot vote in draft election
     */
    public function test_voter_cannot_vote_in_draft_election()
    {
        $draftElection = Election::factory()
            ->state(['status' => 'draft'])
            ->create(['organization_id' => $this->org->id]);

        $voterToken = 'test-voter-token-draft';

        $response = $this->withHeader('X-Voter-Token', $voterToken)
            ->postJson("/api/elections/{$draftElection->id}/vote", [
                'candidate_id' => $this->candidate1->id,
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test voter cannot vote in closed election
     */
    public function test_voter_cannot_vote_in_closed_election()
    {
        $closedElection = Election::factory()
            ->state(['status' => 'closed'])
            ->create(['organization_id' => $this->org->id]);

        $voterToken = 'test-voter-token-closed';

        $response = $this->withHeader('X-Voter-Token', $voterToken)
            ->postJson("/api/elections/{$closedElection->id}/vote", [
                'candidate_id' => $this->candidate1->id,
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test vote requires valid voter token
     */
    public function test_vote_requires_valid_voter_token()
    {
        $response = $this->postJson("/api/elections/{$this->election->id}/vote", [
            'candidate_id' => $this->candidate1->id,
        ]);

        $response->assertStatus(401);
    }
}
