<?php

use App\Models\Car;
use App\Models\Trip;
use App\Models\TripApplication;
use App\Models\User;
use App\Models\Verification;
use Laravel\Sanctum\Sanctum;

function makeApprovedActiveCarForOwner(User $owner): Car
{
    return Car::create([
        'owner_id' => $owner->id,
        'title' => 'Toyota Yaris',
        'make' => 'Toyota',
        'model' => 'Yaris',
        'year' => 2022,
        'license_plate' => 'TEST-' . fake()->unique()->numerify('####'),
        'status' => 'active',
        'approval_status' => 'approved',
        'daily_rate' => 65,
        'deposit_amount' => 0,
        'currency' => 'USD',
    ]);
}

it('blocks unverified driver from applying to posted trip', function () {
    $driver = User::factory()->driver()->create();
    $owner = User::factory()->owner()->create();
    $car = makeApprovedActiveCarForOwner($owner);

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

    Sanctum::actingAs($driver);

    $response = $this->postJson('/api/driver/trip-applications', [
        'trip_id' => $trip->id,
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('trip_applications', [
        'trip_id' => $trip->id,
        'driver_id' => $driver->id,
    ]);
    $this->assertDatabaseHas('trips', [
        'id' => $trip->id,
        'driver_id' => null,
    ]);
});

it('verified driver can apply to posted trip', function () {
    $driver = User::factory()->driver()->create();
    $owner = User::factory()->owner()->create();
    $car = makeApprovedActiveCarForOwner($owner);

    $trip = Trip::create([
        'car_id' => $car->id,
        'owner_id' => $owner->id,
        'driver_id' => null,
        'status' => 'pending',
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(4)->toDateString(),
        'daily_rate' => 80,
        'currency' => 'USD',
        'pickup_location' => 'Yangon',
        'dropoff_location' => 'Mandalay',
    ]);

    Verification::create([
        'user_id' => $driver->id,
        'entity_type' => 'driver',
        'status' => 'approved',
    ]);

    Sanctum::actingAs($driver);

    $response = $this->postJson('/api/driver/trip-applications', [
        'trip_id' => $trip->id,
    ]);

    $response->assertCreated()->assertJson(['success' => true]);

    $tripApplicationId = $response->json('data.id');

    $this->assertDatabaseHas('trip_applications', [
        'id' => $tripApplicationId,
        'trip_id' => $trip->id,
        'car_id' => $car->id,
        'driver_id' => $driver->id,
        'owner_id' => $owner->id,
        'status' => 'active',
    ]);

    $this->assertDatabaseHas('trip_application_events', [
        'trip_application_id' => $tripApplicationId,
        'type' => 'created',
    ]);

    $this->assertDatabaseHas('trips', [
        'id' => $trip->id,
        'driver_id' => $driver->id,
        'owner_id' => $owner->id,
        'status' => 'approved',
    ]);
});

it('driver cannot apply when trip already has another driver', function () {
    $driver = User::factory()->driver()->create();
    $otherDriver = User::factory()->driver()->create();
    $owner = User::factory()->owner()->create();
    $car = makeApprovedActiveCarForOwner($owner);

    Verification::create([
        'user_id' => $driver->id,
        'entity_type' => 'driver',
        'status' => 'approved',
    ]);

    $trip = Trip::create([
        'car_id' => $car->id,
        'driver_id' => $otherDriver->id,
        'owner_id' => $owner->id,
        'status' => 'approved',
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
        'daily_rate' => $car->daily_rate,
        'currency' => $car->currency,
    ]);

    TripApplication::create([
        'trip_id' => $trip->id,
        'car_id' => $car->id,
        'driver_id' => $otherDriver->id,
        'owner_id' => $owner->id,
        'status' => 'active',
        'total_amount' => 120,
        'currency' => 'USD',
        'start_at' => now()->addDay()->toDateTimeString(),
        'end_at' => now()->addDays(3)->toDateTimeString(),
    ]);

    Sanctum::actingAs($driver);

    $response = $this->postJson('/api/driver/trip-applications', [
        'trip_id' => $trip->id,
    ]);

    $response->assertStatus(409);
    $this->assertDatabaseHas('trips', [
        'id' => $trip->id,
        'driver_id' => $otherDriver->id,
    ]);

    expect(TripApplication::query()->where('trip_id', $trip->id)->count())->toBe(1);
});

it('driver index shows open trips and own applied trips', function () {
    $driver = User::factory()->driver()->create();
    $otherDriver = User::factory()->driver()->create();
    $owner = User::factory()->owner()->create();
    $car = makeApprovedActiveCarForOwner($owner);

    $openTrip = Trip::create([
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

    $ownTrip = Trip::create([
        'car_id' => $car->id,
        'owner_id' => $owner->id,
        'driver_id' => $driver->id,
        'status' => 'pending',
        'start_date' => now()->addDays(3)->toDateString(),
        'end_date' => now()->addDays(4)->toDateString(),
        'daily_rate' => 60,
        'currency' => 'USD',
        'pickup_location' => 'C',
        'dropoff_location' => 'D',
    ]);

    $otherTrip = Trip::create([
        'car_id' => $car->id,
        'owner_id' => $owner->id,
        'driver_id' => $otherDriver->id,
        'status' => 'pending',
        'start_date' => now()->addDays(5)->toDateString(),
        'end_date' => now()->addDays(6)->toDateString(),
        'daily_rate' => 70,
        'currency' => 'USD',
        'pickup_location' => 'E',
        'dropoff_location' => 'F',
    ]);

    Sanctum::actingAs($driver);

    $response = $this->getJson('/api/driver/trips');
    $response->assertOk();

    $ids = collect($response->json('data.items'))->pluck('id')->all();

    expect($ids)->toContain($openTrip->id);
    expect($ids)->toContain($ownTrip->id);
    expect($ids)->not->toContain($otherTrip->id);
});

it('driver can only apply to one active trip at a time', function () {
    $driver = User::factory()->driver()->create();
    $owner = User::factory()->owner()->create();
    $car = makeApprovedActiveCarForOwner($owner);

    Verification::create([
        'user_id' => $driver->id,
        'entity_type' => 'driver',
        'status' => 'approved',
    ]);

    $alreadyAppliedTrip = Trip::create([
        'car_id' => $car->id,
        'owner_id' => $owner->id,
        'driver_id' => $driver->id,
        'status' => 'approved',
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
        'daily_rate' => 50,
        'currency' => 'USD',
        'pickup_location' => 'A',
        'dropoff_location' => 'B',
    ]);

    TripApplication::create([
        'trip_id' => $alreadyAppliedTrip->id,
        'car_id' => $car->id,
        'driver_id' => $driver->id,
        'owner_id' => $owner->id,
        'status' => 'active',
        'total_amount' => 120,
        'currency' => 'USD',
        'start_at' => now()->addDay()->toDateTimeString(),
        'end_at' => now()->addDays(2)->toDateTimeString(),
    ]);

    $newOpenTrip = Trip::create([
        'car_id' => $car->id,
        'owner_id' => $owner->id,
        'driver_id' => null,
        'status' => 'pending',
        'start_date' => now()->addDays(3)->toDateString(),
        'end_date' => now()->addDays(4)->toDateString(),
        'daily_rate' => 60,
        'currency' => 'USD',
        'pickup_location' => 'C',
        'dropoff_location' => 'D',
    ]);

    Sanctum::actingAs($driver);

    $response = $this->postJson('/api/driver/trip-applications', [
        'trip_id' => $newOpenTrip->id,
    ]);
    $response->assertStatus(422);

    $this->assertDatabaseHas('trips', [
        'id' => $alreadyAppliedTrip->id,
        'driver_id' => $driver->id,
    ]);

    $this->assertDatabaseHas('trips', [
        'id' => $newOpenTrip->id,
        'driver_id' => null,
    ]);

    $this->assertDatabaseMissing('trip_applications', [
        'trip_id' => $newOpenTrip->id,
        'driver_id' => $driver->id,
    ]);
});
