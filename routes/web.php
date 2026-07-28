<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

// Commented out since we are using API routes and not web routes for authentication. 
// The auth.php routes are not needed in this case.

// require __DIR__.'/auth.php';
