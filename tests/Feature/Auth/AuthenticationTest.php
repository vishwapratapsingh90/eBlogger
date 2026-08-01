<?php

use App\Models\User;

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(200);
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create([
        'email' => 'clark.kent@smallville.com',
        'password' => bcrypt('iamsuperman'),
    ]);

    $loginResponse = $this->post('/api/login', [
        'email' => 'clark.kent@smallville.com',
        'password' => 'iamsuperman',
    ]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $loginResponse->json('token'))
        ->post('/api/logout');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'message',
    ]);
});
