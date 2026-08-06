<?php

use App\Http\Controllers\Api\v1\BlogController;
use App\Http\Controllers\Api\v1\ImagePromptGenerationController;
use App\Http\Controllers\Auth\RegisteredUserController;
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

    Route::get('/users', [RegisteredUserController::class, 'index'])
        ->middleware('auth:sanctum')
        ->name('users.index');

    // API versioning example: all v1 routes use /api/v1/... and share the api.v1.* route name prefix.
    Route::prefix('v1')->name('api.v1.')->group(function () {
        // Named routes for clarity and easier reference
        Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
        Route::get('/blogs/my-blogs', [BlogController::class, 'myBlogs'])->name('blogs.my');
        Route::post('/blogs', [BlogController::class, 'store'])->name('blogs.store');

        // Example: resolve blog by slug using explicit key binding in the route
        // This will use the Blog model's `getRouteKeyName()` if defined, or the {blog:slug} syntax
        Route::get('/blogs/slug/{blog:slug}', [BlogController::class, 'show'])->name('blogs.showBySlug');

        // Default implicit binding (by id)
        Route::get('/blogs/{blog}', [BlogController::class, 'show'])->name('blogs.show');
        Route::put('/blogs/{blog}', [BlogController::class, 'update'])->name('blogs.update');
        Route::delete('/blogs/{blog}', [BlogController::class, 'destroy'])->name('blogs.destroy');

        // Route::apiResource('blogs', BlogController::class);

        Route::apiResource('image-prompt-generations', ImagePromptGenerationController::class)
            ->only(['index', 'store']);
    });

    // Domain routing example: use a dynamic subdomain for API v1 requests.
    // Replace `api.example.com` with your own API domain when needed.
    // Route::domain('{account}.api.example.com')
    //     ->prefix('v1')
    //     ->name('api.v1.')
    //     ->group(function () {
    //         Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
    //         Route::get('/blogs/{blog}', [BlogController::class, 'show'])->name('blogs.show');
    //         Route::post('/blogs', [BlogController::class, 'store'])->name('blogs.store');
    //     });
});

require __DIR__.'/auth.php';
