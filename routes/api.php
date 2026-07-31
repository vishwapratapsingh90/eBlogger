<?php

use App\Http\Controllers\Api\v1\BlogController;
use App\Http\Controllers\Api\v1\ImagePromptGenerationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('v1')->group(function () {
        Route::get('/blogs', [BlogController::class, 'index']);
        Route::get('/blogs/my-blogs', [BlogController::class, 'myBlogs']);
        Route::post('/blogs', [BlogController::class, 'store']);
        Route::get('/blogs/{blog}', [BlogController::class, 'show']);
        Route::put('/blogs/{blog}', [BlogController::class, 'update']);
        Route::delete('/blogs/{blog}', [BlogController::class, 'destroy']);

        // Route::apiResource('blogs', BlogController::class);

        Route::apiResource('image-prompt-generations', ImagePromptGenerationController::class)
            ->only(['index', 'store']);
    });
});

require __DIR__.'/auth.php';
