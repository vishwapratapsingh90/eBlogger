<?php

use App\Models\User;

test('authenticated user can fetch profile from api', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->getJson('/api/user');

    $response
        ->assertOk()
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('email', $user->email)
        ->assertJsonPath('name', $user->name);
});

test('authenticated user can logout and revoke token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this
        ->withToken($token)
        ->postJson('/api/logout');

    $response
        ->assertOk()
        ->assertJsonPath('message', 'User logged out successfully');

    $this->assertNull($user->fresh()?->currentAccessToken());
});

test('unauthenticated user cannot fetch profile data', function () {
    $this->getJson('/api/user')
        ->assertUnauthorized();
});
