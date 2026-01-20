<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\AdminPasswordResetController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\DeletedStaffController;

// ==========================================
// AUTHENTICATION ROUTES (Login/Logout)
// ==========================================
Route::get('/login', function () {
    return view('dashboard'); // Vue SPA entry for admin login
})->name('login');

// Explicit admin login route for password reset redirect
Route::get('/admin/login', function () {
    return view('dashboard'); // Or your actual admin login view/component
})->name('admin.login');

Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// ==========================================
// ADMIN FORGOT / RESET PASSWORD (OWNER/ADMIN)
// ==========================================
Route::get('/admin/password/forgot', [AdminPasswordResetController::class, 'showLinkRequestForm'])
    ->name('admin.password.request');

Route::post('/admin/password/email', [AdminPasswordResetController::class, 'sendResetLinkEmail'])
    ->name('admin.password.email');

Route::get('/admin/password/reset/{token}', [AdminPasswordResetController::class, 'showResetForm'])
    ->name('admin.password.reset');

Route::post('/admin/password/reset', [AdminPasswordResetController::class, 'reset'])
    ->name('admin.password.update');

// ==========================================
// ADMIN ROUTES (Protected by Auth Middleware)
// ==========================================
Route::middleware(['auth'])->group(function () {
    // Admin Dashboard
    Route::get('/admin/dashboard', function () {
        return view('dashboard'); // Vue SPA
    })->name('admin.dashboard');

    // Staff Management Page (Vue SPA entry)
    Route::get('/admin/staff-management', function () {
        return view('dashboard');
    })->name('admin.staff-management');

    // Deleted Staff History Page (SPA entry - Vue Router will load component)
    Route::get('/admin/deleted-staff', function () {
        return view('dashboard');
    })->name('admin.deleted-staff');

    // Admin Panel (Vue SPA entry)
    Route::get('/admin-panel', function () {
        return view('dashboard');
    })->name('admin.panel');
});

// ==========================================
// STAFF ROUTES (Protected by Auth Middleware)
// ==========================================
Route::middleware(['auth'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', function () {
        return view('staff.dashboard');
    })->name('dashboard');
});

// ==========================================
// MANAGER ROUTES (Protected by Auth Middleware)
// ==========================================
Route::middleware(['auth'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', function () {
        return view('manager.dashboard');
    })->name('dashboard');
});

// ==========================================
// API ROUTES FOR DELETED STAFF
// (Role checking handled in controller)
// ==========================================
Route::middleware(['auth'])->group(function () {
    Route::get('/api/admin/deleted-staff', [DeletedStaffController::class, 'apiIndex']);
    Route::post('/api/admin/deleted-staff/{id}/restore', [DeletedStaffController::class, 'apiRestore']);
    Route::delete('/api/admin/deleted-staff/{id}/force', [DeletedStaffController::class, 'apiForceDelete']);
});

// ==========================================
// EXISTING ROUTES (Your SPA & Profile)
// ==========================================
Route::get('/', function () {
    return view('dashboard'); // Vue SPA entry
});

Route::get('/test', function () {
    return 'OK';
});

Route::middleware('auth')->group(function () {
    Route::post('/users/avatar', [ProfileController::class, 'uploadAvatar'])
        ->name('users.avatar');
});

// ==========================================
// SPA CATCH-ALL (MUST BE LAST!)
// ==========================================
Route::view('/{any}', 'dashboard')->where('any', '.*');