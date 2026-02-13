<?php

use App\Models\Car;
use App\Models\Trip;
use App\Models\User;
use App\Models\Verification;
use Laravel\Sanctum\Sanctum;

function makeApprovedCarForTripPosting(User $owner): Car
{
    return Car::create([
        'owner_id' => $owner->id,
        'title' => 'Toyota Belta',
        'make' => 'Toyota',
        'model' => 'Belta',
        'year' => 2021,
        'license_plate' => 'TRIP-' . fake()->unique()->numerify('####'),
        'status' => 'active',
        'approval_status' => 'approved',
        'daily_rate' => 70,
        'deposit_amount' => 0,
        'currency' => 'USD',
    ]);
}

it('owner can post trip', function () {
    $owner = User::factory()->owner()->create();
    $car = makeApprovedCarForTripPosting($owner);
    Verification::create([
        'user_id' => $owner->id,
        'entity_type' => 'owner',
        'status' => 'approved',
    ]);

    Sanctum::actingAs($owner);

    $response = $this->postJson('/api/owner/trips', [
        'car_id' => $car->id,
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
        'daily_rate' => 55,
        'currency' => 'usd',
        'pickup_location' => 'North',
        'dropoff_location' => 'South',
        'notes' => 'Morning trip',
    ]);

    $response->assertCreated()->assertJson(['success' => true]);

    $this->assertDatabaseHas('trips', [
        'id' => $response->json('data.id'),
        'owner_id' => $owner->id,
        'car_id' => $car->id,
        'status' => 'pending',
        'driver_id' => null,
        'daily_rate' => 55,
        'currency' => 'USD',
        'pickup_location' => 'North',
        'dropoff_location' => 'South',
    ]);
});

it('owner can update own pending trip before driver applies', function () {
    $owner = User::factory()->owner()->create();
    $car = makeApprovedCarForTripPosting($owner);
    Verification::create([
        'user_id' => $owner->id,
        'entity_type' => 'owner',
        'status' => 'approved',
    ]);

    $trip = Trip::create([
        'car_id' => $car->id,
        'owner_id' => $owner->id,
        'driver_id' => null,
        'status' => 'pending',
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
        'daily_rate' => 50,
        'currency' => 'USD',
        'pickup_location' => 'A',
        'dropoff_location' => 'B',
    ]);

    Sanctum::actingAs($owner);

    $response = $this->patchJson("/api/owner/trips/{$trip->id}", [
        'daily_rate' => 75,
        'pickup_location' => 'Downtown',
        'dropoff_location' => 'Airport',
        'notes' => 'Updated route',
    ]);

    $response->assertOk()->assertJson(['success' => true]);

    $this->assertDatabaseHas('trips', [
        'id' => $trip->id,
        'daily_rate' => 75,
        'pickup_location' => 'Downtown',
        'dropoff_location' => 'Airport',
        'notes' => 'Updated route',
    ]);
});

it('owner cannot update trip after driver has applied', function () {
    $owner = User::factory()->owner()->create();
    $driver = User::factory()->driver()->create();
    $car = makeApprovedCarForTripPosting($owner);
    Verification::create([
        'user_id' => $owner->id,
        'entity_type' => 'owner',
        'status' => 'approved',
    ]);

    $trip = Trip::create([
        'car_id' => $car->id,
        'owner_id' => $owner->id,
        'driver_id' => $driver->id,
        'status' => 'pending',
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
        'daily_rate' => 50,
        'currency' => 'USD',
        'pickup_location' => 'A',
        'dropoff_location' => 'B',
    ]);

    Sanctum::actingAs($owner);

    $response = $this->patchJson("/api/owner/trips/{$trip->id}", [
        'daily_rate' => 85,
    ]);

    $response->assertStatus(422);
});

it('unverified owner cannot post trip', function () {
    $owner = User::factory()->owner()->create();
    $car = makeApprovedCarForTripPosting($owner);

    Sanctum::actingAs($owner);

    $response = $this->postJson('/api/owner/trips', [
        'car_id' => $car->id,
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
        'daily_rate' => 50,
        'currency' => 'USD',
        'pickup_location' => 'North',
        'dropoff_location' => 'South',
    ]);

    $response->assertStatus(403);
});
