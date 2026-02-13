<?php

namespace Database\Factories;

use App\Models\Trip;
use App\Models\TripApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TripApplication>
 */
class TripApplicationFactory extends Factory
{
    protected $model = TripApplication::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trip_id' => Trip::factory()->approved(),
            'car_id' => null,
            'driver_id' => null,
            'owner_id' => null,
            'status' => 'active',
            'total_amount' => fake()->numberBetween(100, 500),
            'currency' => 'USD',
            'start_at' => now(),
            'end_at' => null,
            'pickup_location' => fake()->streetAddress(),
            'dropoff_location' => fake()->streetAddress(),
            'contract_terms' => fake()->sentence(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed', 'end_at' => now()->addDays(2)]);
    }

    public function configure()
    {
        return $this->afterCreating(function ($tripApplication) {
            if ($tripApplication->trip) {
                $tripApplication->car_id = $tripApplication->trip->car_id;
                $tripApplication->driver_id = $tripApplication->trip->driver_id;
                $tripApplication->owner_id = $tripApplication->trip->owner_id;
                $tripApplication->save();
            }
        });
    }
}
