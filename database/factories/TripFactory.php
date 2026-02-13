<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trip>
 */
class TripFactory extends Factory
{
    protected $model = Trip::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'car_id' => Car::factory()->approved(),
            'driver_id' => User::factory()->driver(),
            'owner_id' => null,
            'status' => 'pending',
            'start_date' => now()->addDays(1)->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'daily_rate' => fake()->numberBetween(20, 150),
            'currency' => 'USD',
            'notes' => fake()->sentence(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => 'approved']);
    }

    public function configure()
    {
        return $this->afterCreating(function ($trip) {
            if ($trip->car && !$trip->owner_id) {
                $trip->owner_id = $trip->car->owner_id;
                $trip->save();
            }
        });
    }
}
