<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\Admin\StaffController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Models\User;  // ← ADDED: Import your User model

// API routes using session (web guard)
Route::middleware('web')->group(function () {
    // ==========================================
    // AUTH & PROFILE ROUTES
    // ==========================================
    Route::post('/login',           [AuthController::class, 'login']);
    Route::post('/logout',          [AuthController::class, 'logout']);

    // FIXED: Forgot Password Routes (no auth needed)
    Route::post('/forgot-password', function (Request $request) {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')  // FIXED: only 1 arg (array)
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Reset link sent to your email!'])
            : response()->json(['error' => 'Unable to send reset link'], 400);
    });

    Route::post('/reset-password', function (Request $request) {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),  // FIXED: array only
            function (User $user, string $password) {
                $user->password = $password; // Triggers mutator, hashes and saves to password_hash
                $user->setRememberToken(Str::random(60));
                $user->save();
                // event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password reset successfully!'])
            : response()->json(['error' => 'Token invalid or expired'], 400);
    });

    Route::get('/me',               [AuthController::class, 'me']);
    Route::get('/owner-profile',    [AuthController::class, 'ownerProfile']);
    Route::put('/owner-profile',    [AuthController::class, 'updateOwnerProfile']);
    Route::post('/upload-avatar',   [AuthController::class, 'uploadAvatar']);

    Route::get('/owner-dashboard', [OwnerDashboardController::class, 'index']);

    // ==========================================
    // STAFF MANAGEMENT API
    // ==========================================
    Route::prefix('admin')->group(function () {
        Route::get('/staff',            [StaffController::class, 'apiIndex']);
        Route::get('/staff/{id}',       [StaffController::class, 'apiShow']);
        Route::post('/staff',           [StaffController::class, 'apiStore']);
        Route::put('/staff/{id}',       [StaffController::class, 'apiUpdate']);
        Route::delete('/staff/{id}',    [StaffController::class, 'apiDestroy']);
        Route::get('/branches',         [StaffController::class, 'apiBranches']);
    });
});
