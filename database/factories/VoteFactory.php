<?php

namespace Database\Factories;

use App\Models\Vote;
use App\Models\VoteSession;
use App\Models\Position;
use App\Models\Candidate;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoteFactory extends Factory
{
    protected $model = Vote::class;

    public function definition(): array
    {
        $voteSession = VoteSession::factory();
        $position = Position::factory();

        return [
            'vote_session_id' => $voteSession,
            'position_id' => $position,
            'candidate_id' => Candidate::factory(['position_id' => $position]),
        ];
    }
}
