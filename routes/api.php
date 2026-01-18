<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\Admin\StaffController;

// API routes using session (web guard)
Route::middleware('web')->group(function () {
    // ==========================================
    // AUTH & PROFILE ROUTES
    // ==========================================
    Route::post('/login',          [AuthController:: class, 'login']);
    Route::post('/logout',         [AuthController::class, 'logout']);

    Route::get('/me',              [AuthController::class, 'me']);
    Route::get('/owner-profile',   [AuthController::class, 'ownerProfile']);
    Route::put('/owner-profile',   [AuthController:: class, 'updateOwnerProfile']);
    Route::post('/upload-avatar',  [AuthController:: class, 'uploadAvatar']);

    Route::get('/owner-dashboard', [OwnerDashboardController::class, 'index']);

    // ==========================================
    // STAFF MANAGEMENT API
    // These routes use the session (web) guard and CSRF protection via Sanctum.
    // ==========================================
    Route::prefix('admin')->group(function () {
        Route::get('/staff',           [StaffController::class, 'apiIndex']);
        Route::get('/staff/{id}',      [StaffController::class, 'apiShow']);
        Route::post('/staff',          [StaffController:: class, 'apiStore']);
        Route::put('/staff/{id}',      [StaffController:: class, 'apiUpdate']);
        Route::delete('/staff/{id}',   [StaffController:: class, 'apiDestroy']);
        Route::get('/branches',        [StaffController::class, 'apiBranches']);
    });
});

// (moved to `web` group above to use Sanctum/session authentication)
