<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Car>
 */
class CarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory()->owner(),
            'title' => fake()->words(3, true),
            'make' => fake()->randomElement(['Toyota', 'Honda', 'Ford']),
            'model' => fake()->word(),
            'year' => fake()->numberBetween(2015, 2024),
            'license_plate' => strtoupper(fake()->bothify('??###??')),
            'status' => 'pending_review',
            'approval_status' => 'pending',
            'daily_rate' => fake()->numberBetween(20, 150),
            'deposit_amount' => fake()->numberBetween(100, 500),
            'currency' => 'USD',
            'description' => fake()->sentence(),
            'pickup_latitude' => fake()->latitude(),
            'pickup_longitude' => fake()->longitude(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => 'active',
            'approval_status' => 'approved',
        ]);
    }
}
