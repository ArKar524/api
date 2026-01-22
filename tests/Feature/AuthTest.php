<?php

use App\Models\User;

it('registers owner successfully', function () {
    $payload = [
        'name' => 'Owner One',
        'phone' => '5550001',
        'email' => 'owner1@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'owner',
    ];

    $response = $this->postJson('/api/auth/register', $payload);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'user' => ['phone' => '5550001', 'role' => 'owner'],
            ],
            'errors' => null,
        ]);

    $this->assertDatabaseHas('users', ['phone' => '5550001', 'role' => 'owner']);
});

it('does not allow setting admin role on register', function () {
    $payload = [
        'name' => 'Bad Admin',
        'phone' => '5559999',
        'email' => 'bad@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'admin',
    ];

    $response = $this->postJson('/api/auth/register', $payload);

    $response->assertCreated();
    $this->assertDatabaseHas('users', ['phone' => '5559999', 'role' => 'driver']);
});

it('logs in by phone and email', function () {
    $user = User::factory()->create([
        'phone' => '5551000',
        'email' => 'user@example.com',
        'password' => bcrypt('secret123'),
    ]);

    $responsePhone = $this->postJson('/api/auth/login', [
        'login' => '5551000',
        'password' => 'secret123',
    ]);
    $responsePhone->assertOk()->assertJson(['success' => true])->assertJsonStructure(['data' => ['token']]);

    $responseEmail = $this->postJson('/api/auth/login', [
        'login' => 'user@example.com',
        'password' => 'secret123',
    ]);
    $responseEmail->assertOk()->assertJson(['success' => true])->assertJsonStructure(['data' => ['token']]);
});

it('fails login with wrong password', function () {
    User::factory()->create(['phone' => '5552000', 'password' => bcrypt('rightpass')]);

    $response = $this->postJson('/api/auth/login', [
        'login' => '5552000',
        'password' => 'wrong',
    ]);

    $response->assertStatus(401)->assertJson(['success' => false]);
});

it('returns me and revokes token on logout', function () {
    $user = User::factory()->create(['phone' => '5553000']);
    $token = $user->createToken('api')->plainTextToken;

    $me = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/auth/me');
    $me->assertOk()->assertJson([
        'success' => true,
        'data' => ['user' => ['phone' => '5553000']],
    ]);

    $logout = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/auth/logout');
    $logout->assertOk()->assertJson(['success' => true]);

    $me2 = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/auth/me');
    $me2->assertStatus(401);
});
