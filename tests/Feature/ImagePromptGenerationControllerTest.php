<?php

use App\Models\ImageGeneration;
use App\Models\User;

it('returns pagination metadata for image prompt generations', function () {
    $user = User::factory()->create();

    ImageGeneration::create([
        'user_id' => $user->id,
        'image_path' => 'uploads/images/test-1.jpg',
        'generated_prompt' => 'A test prompt',
        'original_filename' => 'test-1.jpg',
        'file_size' => 1024,
        'mime_type' => 'image/jpeg',
    ]);

    ImageGeneration::create([
        'user_id' => $user->id,
        'image_path' => 'uploads/images/test-2.jpg',
        'generated_prompt' => 'Another test prompt',
        'original_filename' => 'test-2.jpg',
        'file_size' => 2048,
        'mime_type' => 'image/jpeg',
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson('/api/v1/image-prompt-generations?per_page=1');

    $response->assertOk()
        ->assertJsonStructure([
            'data',
            'links',
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

it('ignores invalid sort parameters', function () {
    $user = User::factory()->create();

    ImageGeneration::create([
        'user_id' => $user->id,
        'image_path' => 'uploads/images/test-1.jpg',
        'generated_prompt' => 'A test prompt',
        'original_filename' => 'test-1.jpg',
        'file_size' => 1024,
        'mime_type' => 'image/jpeg',
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson('/api/v1/image-prompt-generations?sort_by=1&sort_order=desc');

    $response->assertOk();
});
