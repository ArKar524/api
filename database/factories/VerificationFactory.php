<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Verification>
 */
class VerificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->owner(),
            'entity_type' => 'owner',
            'status' => 'pending',
            'requested_at' => now(),
            'completed_at' => null,
            'notes' => null,
        ];
    }

    public function driver(): static
    {
        return $this->state(fn () => ['entity_type' => 'driver', 'user_id' => User::factory()->driver()]);
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => 'approved', 'completed_at' => now()]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => ['status' => 'rejected', 'completed_at' => now(), 'notes' => 'Rejected']);
    }
}
