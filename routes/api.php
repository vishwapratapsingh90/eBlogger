<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::get('/blogs', [App\Http\Controllers\Api\v1\BlogController::class, 'index']);
    Route::post('/blogs', [App\Http\Controllers\Api\v1\BlogController::class, 'store']);
    Route::get('/blogs/{id}', [App\Http\Controllers\Api\v1\BlogController::class, 'show']);
    Route::put('/blogs/{id}', [App\Http\Controllers\Api\v1\BlogController::class, 'update']);
    Route::delete('/blogs/{id}', [App\Http\Controllers\Api\v1\BlogController::class, 'destroy']);
});