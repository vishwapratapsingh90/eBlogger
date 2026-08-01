<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

beforeEach(function () {
    $throttleKey = Str::transliterate('bruce.wayne@gothamcity.com'.'|'.'127.0.0.1');
    RateLimiter::clear($throttleKey);
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('test users can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'bruce.wayne@gothamcity.com',
        'password' => bcrypt('iambatman'),
    ]);

    $response = $this->post('/api/login', [
        'email' => 'bruce.wayne@gothamcity.com',
        'password' => 'iambatman',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'message',
        'user' => [
            'id',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
        ],
        'token',
    ]);
});

test('test login rate limiting', function () {
    $user = User::factory()->create([
        'email' => 'bruce.wayne@gothamcity.com',
        'password' => bcrypt('iambatman'),
    ]);

    // Simulate multiple failed login attempts
    for ($i = 0; $i < 5; $i++) {
        $this->post('/api/login', [
            'email' => 'bruce.wayne@gothamcity.com',
            'password' => 'wrong-password',
        ]);
    }

    $response = $this->post('/api/login', [
        'email' => 'bruce.wayne@gothamcity.com',
        'password' => 'iambatman',
    ]);

    $response->assertStatus(422);
});

test('test after rate limit reset, user can login successfully', function () {
    $user = User::factory()->create([
        'email' => 'bruce.wayne@gothamcity.com',
        'password' => bcrypt('iambatman'),
    ]);

    // Simulate multiple failed login attempts
    for ($i = 0; $i < 5; $i++) {
        $this->post('/api/login', [
            'email' => 'bruce.wayne@gothamcity.com',
            'password' => 'wrong-password',
        ]);
    }

    // Clear the rate limit
    $throttleKey = Str::transliterate('bruce.wayne@gothamcity.com'.'|'.'127.0.0.1');
    RateLimiter::clear($throttleKey);

    $response = $this->post('/api/login', [
        'email' => 'bruce.wayne@gothamcity.com',
        'password' => 'iambatman',
    ]);

    $response->assertStatus(200);
});

test('test logged in user trying to login again', function () {
    $user = User::factory()->create([
        'email' => 'bruce.wayne@gothamcity.com',
        'password' => bcrypt('iambatman'),
    ]);

    $response = $this->actingAs($user, 'web')->post('/api/login', [
        'email' => 'bruce.wayne@gothamcity.com',
        'password' => 'iambatman',
    ]);

    $response->assertStatus(302);
});
