<?php

use App\Http\Controllers\Api\UserProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/auth/vkid', [UserProfileController::class, 'vkidAuth']);

// Protected routes (JWT required)
Route::middleware('jwt.auth')->group(function () {
    Route::get('/user/profile', [UserProfileController::class, 'profile']);
});
