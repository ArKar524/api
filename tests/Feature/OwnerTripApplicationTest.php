<?php

use App\Models\Car;
use App\Models\Trip;
use App\Models\TripApplication;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function makeApprovedCarForOwnerTripApplicationTests(User $owner): Car
{
    return Car::create([
        'owner_id' => $owner->id,
        'title' => 'Honda Fit',
        'make' => 'Honda',
        'model' => 'Fit',
        'year' => 2021,
        'license_plate' => 'OWNR-' . fake()->unique()->numerify('####'),
        'status' => 'active',
        'approval_status' => 'approved',
        'daily_rate' => 80,
        'deposit_amount' => 0,
        'currency' => 'USD',
    ]);
}

it('owner cannot create trip application directly', function () {
    $owner = User::factory()->owner()->create();
    Sanctum::actingAs($owner);

    $response = $this->postJson('/api/owner/trip-applications', [
        'trip_id' => 1,
    ]);

    $response->assertStatus(405);
});

it('owner can list own trip applications', function () {
    $ownerA = User::factory()->owner()->create();
    $ownerB = User::factory()->owner()->create();
    $driver = User::factory()->driver()->create();
    $carA = makeApprovedCarForOwnerTripApplicationTests($ownerA);
    $carB = makeApprovedCarForOwnerTripApplicationTests($ownerB);

    $tripA = Trip::create([
        'car_id' => $carA->id,
        'driver_id' => $driver->id,
        'owner_id' => $ownerA->id,
        'status' => 'approved',
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
        'daily_rate' => $carA->daily_rate,
        'currency' => 'USD',
    ]);

    $tripB = Trip::create([
        'car_id' => $carB->id,
        'driver_id' => $driver->id,
        'owner_id' => $ownerB->id,
        'status' => 'approved',
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
        'daily_rate' => $carB->daily_rate,
        'currency' => 'USD',
    ]);

    $ownerAApplication = TripApplication::create([
        'trip_id' => $tripA->id,
        'car_id' => $carA->id,
        'driver_id' => $driver->id,
        'owner_id' => $ownerA->id,
        'status' => 'active',
        'total_amount' => 120,
        'currency' => 'USD',
        'start_at' => now()->addDay()->toDateTimeString(),
        'end_at' => now()->addDays(3)->toDateTimeString(),
    ]);

    $ownerBApplication = TripApplication::create([
        'trip_id' => $tripB->id,
        'car_id' => $carB->id,
        'driver_id' => $driver->id,
        'owner_id' => $ownerB->id,
        'status' => 'active',
        'total_amount' => 120,
        'currency' => 'USD',
        'start_at' => now()->addDay()->toDateTimeString(),
        'end_at' => now()->addDays(3)->toDateTimeString(),
    ]);

    Sanctum::actingAs($ownerA);

    $response = $this->getJson('/api/owner/trip-applications');
    $response->assertOk();

    $ids = collect($response->json('data.items'))->pluck('id')->all();

    expect($ids)->toContain($ownerAApplication->id);
    expect($ids)->not->toContain($ownerBApplication->id);
});

it('owner can update own trip application', function () {
    $owner = User::factory()->owner()->create();
    $driver = User::factory()->driver()->create();
    $car = makeApprovedCarForOwnerTripApplicationTests($owner);

    $trip = Trip::create([
        'car_id' => $car->id,
        'driver_id' => $driver->id,
        'owner_id' => $owner->id,
        'status' => 'approved',
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
        'daily_rate' => $car->daily_rate,
        'currency' => 'USD',
    ]);

    $tripApplication = TripApplication::create([
        'trip_id' => $trip->id,
        'car_id' => $car->id,
        'driver_id' => $driver->id,
        'owner_id' => $owner->id,
        'status' => 'active',
        'total_amount' => 150,
        'currency' => 'USD',
        'start_at' => now()->addDay()->toDateTimeString(),
        'end_at' => now()->addDays(3)->toDateTimeString(),
    ]);

    Sanctum::actingAs($owner);

    $response = $this->patchJson("/api/owner/trip-applications/{$tripApplication->id}", [
        'status' => 'completed',
        'total_amount' => 220,
        'pickup_location' => 'Center',
        'dropoff_location' => 'Station',
    ]);

    $response->assertOk()->assertJson([
        'success' => true,
        'message' => 'Trip application updated.',
    ]);

    $this->assertDatabaseHas('trip_applications', [
        'id' => $tripApplication->id,
        'status' => 'completed',
        'total_amount' => 220,
        'pickup_location' => 'Center',
        'dropoff_location' => 'Station',
    ]);

    $this->assertDatabaseHas('trip_application_events', [
        'trip_application_id' => $tripApplication->id,
        'type' => 'completed',
    ]);
});

it('owner cannot update trip application owned by another owner', function () {
    $ownerA = User::factory()->owner()->create();
    $ownerB = User::factory()->owner()->create();
    $driver = User::factory()->driver()->create();
    $car = makeApprovedCarForOwnerTripApplicationTests($ownerA);

    $trip = Trip::create([
        'car_id' => $car->id,
        'driver_id' => $driver->id,
        'owner_id' => $ownerA->id,
        'status' => 'approved',
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
        'daily_rate' => $car->daily_rate,
        'currency' => 'USD',
    ]);

    $tripApplication = TripApplication::create([
        'trip_id' => $trip->id,
        'car_id' => $car->id,
        'driver_id' => $driver->id,
        'owner_id' => $ownerA->id,
        'status' => 'active',
        'total_amount' => 120,
        'currency' => 'USD',
        'start_at' => now()->addDay()->toDateTimeString(),
        'end_at' => now()->addDays(3)->toDateTimeString(),
    ]);

    Sanctum::actingAs($ownerB);

    $response = $this->patchJson("/api/owner/trip-applications/{$tripApplication->id}", [
        'status' => 'cancelled',
    ]);

    $response->assertStatus(403);
});
