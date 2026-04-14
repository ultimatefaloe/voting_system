<?php

namespace Database\Factories;

use App\Models\VoteSession;
use App\Models\Election;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoteSessionFactory extends Factory
{
    protected $model = VoteSession::class;

    public function definition(): array
    {
        return [
            'election_id' => Election::factory(),
            'voter_token' => 'voter_' . bin2hex(random_bytes(12)),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'submitted_at' => now(),
        ];
    }
}
