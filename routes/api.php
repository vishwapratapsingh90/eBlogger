<?php

use App\Http\Controllers\Api\v1\BlogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware(['auth:sanctum'])->group(function () {
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
    });
});



require __DIR__.'/auth.php';
