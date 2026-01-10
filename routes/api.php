<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\OwnerDashboardController;

// API routes na gumagamit ng session (web guard)
Route::middleware('web')->group(function () {
    Route::post('/login',          [AuthController::class, 'login']);
    Route::post('/logout',         [AuthController::class, 'logout']);

    Route::get('/me',              [AuthController::class, 'me']);
    Route::get('/owner-profile',   [AuthController::class, 'ownerProfile']);
    Route::put('/owner-profile',   [AuthController::class, 'updateOwnerProfile']);
    Route::post('/upload-avatar',  [AuthController::class, 'uploadAvatar']);

    Route::get('/owner-dashboard', [OwnerDashboardController::class, 'index']);
});
