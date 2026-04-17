<?php

namespace Database\Factories;

use App\Models\Candidate;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

class CandidateFactory extends Factory
{
    protected $model = Candidate::class;

    public function definition(): array
    {
        return [
            'position_id' => Position::factory(),
            'name' => $this->faker->name(),
            'bio' => $this->faker->paragraph(),
            'avatar' => $this->faker->imageUrl(200, 200, 'people', true),
            'order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
