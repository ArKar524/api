<?php

use App\Models\Car;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

it('unverified owner cannot create car', function () {
    Storage::fake('public');
    $owner = User::factory()->owner()->create();
    Sanctum::actingAs($owner);

    $payload = [
        'title' => 'Car A',
        'make' => 'Toyota',
        'model' => 'Vitz',
        'year' => 2022,
        'plate_number' => 'ABC123',
        'daily_price' => 50,
        'pickup_lat' => 1,
        'pickup_lng' => 1,
        'photos' => [
            UploadedFile::fake()->image('p1.jpg'),
            UploadedFile::fake()->image('p2.jpg'),
            UploadedFile::fake()->image('p3.jpg'),
        ],
    ];

    $response = $this->postJson('/api/owner/cars', $payload);
    $response->assertStatus(403);
});

it('verified owner can create car with photos', function () {
    Storage::fake('public');
    $owner = User::factory()->owner()->create();
    Verification::factory()->approved()->create(['user_id' => $owner->id, 'entity_type' => 'owner']);
    Sanctum::actingAs($owner);

    $payload = [
        'title' => 'Car B',
        'make' => 'Toyota',
        'model' => 'Yaris',
        'year' => 2021,
        'plate_number' => 'XYZ789',
        'daily_price' => 60,
        'pickup_lat' => 1,
        'pickup_lng' => 1,
        'photos' => [
            UploadedFile::fake()->image('p1.jpg'),
            UploadedFile::fake()->image('p2.jpg'),
            UploadedFile::fake()->image('p3.jpg'),
        ],
    ];

    $response = $this->postJson('/api/owner/cars', $payload);
    $response->assertCreated()->assertJson(['success' => true]);
    $this->assertDatabaseHas('cars', ['plate_number' => 'XYZ789', 'status' => 'pending_review']);
});

it('admin can approve car', function () {
    $car = Car::factory()->create(['approval_status' => 'pending', 'status' => 'pending_review']);
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->postJson("/api/admin/cars/{$car->id}/review", ['status' => 'approved']);
    $response->assertOk();
    $this->assertDatabaseHas('cars', ['id' => $car->id, 'approval_status' => 'approved']);
});

it('non admin cannot approve car', function () {
    $car = Car::factory()->create(['approval_status' => 'pending']);
    $owner = User::factory()->owner()->create();
    Sanctum::actingAs($owner);

    $response = $this->postJson("/api/admin/cars/{$car->id}/review", ['status' => 'approved']);
    $response->assertStatus(403);
});
