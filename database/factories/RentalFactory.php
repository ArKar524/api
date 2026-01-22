<?php

namespace Database\Factories;

use App\Models\RentalRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rental>
 */
class RentalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rental_request_id' => RentalRequest::factory()->approved(),
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
        return $this->afterCreating(function ($rental) {
            if ($rental->rentalRequest) {
                $rental->car_id = $rental->rentalRequest->car_id;
                $rental->driver_id = $rental->rentalRequest->driver_id;
                $rental->owner_id = $rental->rentalRequest->owner_id;
                $rental->save();
            }
        });
    }
}
