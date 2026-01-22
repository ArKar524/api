<?php

use App\Models\User;
use App\Models\Verification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

it('owner submits kyc successfully', function () {
    Storage::fake('public');
    $owner = User::factory()->owner()->create();
    Sanctum::actingAs($owner);

    $payload = [
        'nrc_front' => UploadedFile::fake()->image('nrc_front.jpg'),
        'nrc_back' => UploadedFile::fake()->image('nrc_back.jpg'),
        'selfie' => UploadedFile::fake()->image('selfie.jpg'),
    ];

    $response = $this->postJson('/api/owner/kyc', $payload);

    $response->assertCreated()->assertJson(['success' => true]);
    $this->assertDatabaseHas('verifications', ['user_id' => $owner->id, 'entity_type' => 'owner', 'status' => 'pending']);
    Storage::disk('public')->assertExists('verifications/'.$response->json('data.id'));
});

it('driver submits kyc successfully', function () {
    Storage::fake('public');
    $driver = User::factory()->driver()->create();
    Sanctum::actingAs($driver);

    $payload = [
        'license_front' => UploadedFile::fake()->image('license_front.jpg'),
        'license_back' => UploadedFile::fake()->image('license_back.jpg'),
        'nrc_front' => UploadedFile::fake()->image('nrc_front.jpg'),
        'nrc_back' => UploadedFile::fake()->image('nrc_back.jpg'),
        'selfie' => UploadedFile::fake()->image('selfie.jpg'),
    ];

    $response = $this->postJson('/api/driver/kyc', $payload);

    $response->assertCreated()->assertJson(['success' => true]);
    $this->assertDatabaseHas('verifications', ['user_id' => $driver->id, 'entity_type' => 'driver']);
});

it('kyc missing file returns 422', function () {
    Storage::fake('public');
    $owner = User::factory()->owner()->create();
    Sanctum::actingAs($owner);

    $payload = [
        'nrc_front' => UploadedFile::fake()->image('nrc_front.jpg'),
        'nrc_back' => UploadedFile::fake()->image('nrc_back.jpg'),
        // missing selfie
    ];

    $response = $this->postJson('/api/owner/kyc', $payload);
    $response->assertStatus(422);
});

it('admin can approve verification', function () {
    $verification = Verification::factory()->create();
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $response = $this->postJson("/api/admin/verifications/{$verification->id}/review", ['status' => 'approved']);
    $response->assertOk()->assertJson(['success' => true]);
    $this->assertDatabaseHas('verifications', ['id' => $verification->id, 'status' => 'approved']);
});

it('non admin cannot review verification', function () {
    $verification = Verification::factory()->create();
    $owner = User::factory()->owner()->create();
    Sanctum::actingAs($owner);

    $response = $this->postJson("/api/admin/verifications/{$verification->id}/review", ['status' => 'approved']);
    $response->assertStatus(403);
});
