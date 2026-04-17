<?php

namespace Database\Factories;

use App\Models\Election;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionFactory extends Factory
{
    protected $model = Position::class;

    public function definition(): array
    {
        return [
            'election_id' => Election::factory(),
            'title' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'max_votes' => $this->faker->numberBetween(1, 3),
            'order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
