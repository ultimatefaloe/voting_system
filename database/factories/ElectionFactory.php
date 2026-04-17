<?php

namespace Database\Factories;

use App\Models\Election;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ElectionFactory extends Factory
{
    protected $model = Election::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('+1 week', '+2 weeks');
        $endDate = $this->faker->dateTimeBetween($startDate, '+1 month');

        return [
            'organization_id' => Organization::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['public', 'private']),
            'status' => 'draft',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'access_token' => null,
            'created_by' => User::factory(),
        ];
    }

    public function active(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
                'start_date' => now()->subHours(2),
                'end_date' => now()->addDay(),
            ];
        });
    }

    public function closed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'closed',
                'start_date' => now()->subDay(),
                'end_date' => now()->subHours(1),
            ];
        });
    }

    public function private(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'private',
                'access_token' => Election::generateAccessToken(),
            ];
        });
    }
}
