<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\StaffController;

// ==========================================
// AUTHENTICATION ROUTES (Login/Logout)
// ==========================================
Route::get('/login', [LoginController:: class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController:: class, 'login'])->name('login.post');
Route::get('/logout', [LoginController:: class, 'logout'])->name('logout');

// ==========================================
// ADMIN ROUTES (Protected by Admin Middleware)
// ==========================================
Route::middleware(['auth'])->group(function () {
    // Admin Dashboard
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Staff Management Page (Vue)
    Route::get('/admin/staff-management', function () {
        return view('admin.dashboard');
    })->name('admin.staff-management');


    });

// ==========================================
// STAFF ROUTES (Protected by Auth Middleware)
// ==========================================
Route::middleware(['auth'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', function () {
        return view('staff. dashboard');
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
// EXISTING ROUTES (Your SPA & Profile)
// ==========================================
Route::get('/', function () {
    return view('dashboard'); // ito ang entry ng SPA mo
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
Route:: view('/{any}', 'dashboard')
    ->where('any', '.*');
