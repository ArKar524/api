<?php

use App\Models\Car;
use App\Models\RentalRequest;
use App\Models\User;
use App\Models\Verification;
use Laravel\Sanctum\Sanctum;

it('driver cannot apply if not verified', function () {
    $driver = User::factory()->driver()->create();
    $car = Car::factory()->approved()->create();
    Sanctum::actingAs($driver);

    $response = $this->postJson('/api/rental-requests', [
        'car_id' => $car->id,
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
    ]);

    $response->assertStatus(403);
});

it('verified driver can apply to approved car', function () {
    $driver = User::factory()->driver()->create();
    Verification::factory()->driver()->approved()->create(['user_id' => $driver->id]);
    $car = Car::factory()->approved()->create();
    Sanctum::actingAs($driver);

    $response = $this->postJson('/api/rental-requests', [
        'car_id' => $car->id,
        'start_date' => now()->addDay()->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
    ]);

    $response->assertCreated()->assertJson(['success' => true]);
});

it('owner can approve rental request and rental created', function () {
    $owner = User::factory()->owner()->create();
    $car = Car::factory()->approved()->create(['owner_id' => $owner->id]);
    $driver = User::factory()->driver()->create();
    $request = RentalRequest::factory()->create([
        'car_id' => $car->id,
        'driver_id' => $driver->id,
        'owner_id' => $owner->id,
        'status' => 'pending',
    ]);

    Sanctum::actingAs($owner);
    $response = $this->postJson("/api/rental-requests/{$request->id}/approve");
    $response->assertOk();
    $this->assertDatabaseHas('rentals', ['rental_request_id' => $request->id]);
});
