<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\DeletedStaffController;

// ==========================================
// AUTHENTICATION ROUTES (Login/Logout)
// ==========================================
Route::get('/login', function () {
    return view('dashboard');
})->name('login');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// ==========================================
// ADMIN ROUTES (Protected by Auth Middleware)
// ==========================================
Route::middleware(['auth'])->group(function () {
    // Admin Dashboard
    Route::get('/admin/dashboard', function () {
        return view('dashboard');
    })->name('admin.dashboard');

    // Staff Management Page (Vue SPA entry)
    Route::get('/admin/staff-management', function () {
        return view('dashboard');
    })->name('admin.staff-management');

    // Deleted Staff History Page (SPA entry - Vue Router will load component)
    Route::get('/admin/deleted-staff', function () {
        return view('dashboard');  // ← SPA entry (not a separate blade)
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
    return view('dashboard');
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
Route::view('/{any}', 'dashboard')
    ->where('any', '.*');
