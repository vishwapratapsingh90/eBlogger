<?php

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
        Route::get('/blogs', [\App\Http\Controllers\Api\v1\BlogController::class, 'index']);
        Route::post('/blogs', [\App\Http\Controllers\Api\v1\BlogController::class, 'store']);
        Route::get('/blogs/{id}', [\App\Http\Controllers\Api\v1\BlogController::class, 'show']);
        Route::put('/blogs/{id}', [\App\Http\Controllers\Api\v1\BlogController::class, 'update']);
        Route::delete('/blogs/{id}', [\App\Http\Controllers\Api\v1\BlogController::class, 'destroy']);
    });
});



require __DIR__.'/auth.php';
