<?php

use App\Models\User;

test('authenticated user can fetch current profile', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->getJson('/api/user');

    $response
        ->assertOk()
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('email', $user->email);
});

test('unauthenticated user cannot fetch profile', function () {
    $this->getJson('/api/user')
        ->assertUnauthorized();
});
