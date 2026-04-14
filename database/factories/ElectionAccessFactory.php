<?php

namespace Database\Factories;

use App\Models\Election;
use App\Models\ElectionAccess;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ElectionAccessFactory extends Factory
{
    protected $model = ElectionAccess::class;

    public function definition(): array
    {
        return [
            'election_id' => Election::factory(),
            'user_id' => $this->faker->boolean() ? User::factory() : null,
            'email' => $this->faker->safeEmail(),
            'token' => ElectionAccess::generateToken(),
            'status' => 'active',
            'expires_at' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'used_at' => null,
        ];
    }

    public function used(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'used',
                'used_at' => now(),
            ];
        });
    }

    public function noExpiry(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'expires_at' => null,
            ];
        });
    }
}
