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
// Helper to return views with no-cache headers (prevents back-button cached pages)
if (! function_exists('no_cache_view')) {
    function no_cache_view($view)
    {
        return response()->view($view)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}

Route::get('/login', function () {
    return no_cache_view('dashboard'); // Vue SPA entry for admin login
})->name('login')->middleware('web');

// Explicit admin login route for password reset redirect
Route::get('/admin/login', function () {
    return no_cache_view('dashboard'); // Or your actual admin login view/component
})->name('admin.login')->middleware('web');

// Admin panel - with cache control to prevent back button issues
Route::get('/admin-panel', function () {
    return response()
        ->view('dashboard')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
})->name('admin.dashboard')->middleware('web');

// Manager panel
Route::get('/manager-panel', function () {
    return response()
        ->view('dashboard')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
})->name('manager.dashboard')->middleware('web');

// Staff panel
Route::get('/staff-panel', function () {
    return response()
        ->view('dashboard')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
})->name('staff.dashboard')->middleware('web');

// HR panel
Route::get('/hr-panel', function () {
    return response()
        ->view('dashboard')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
})->name('hr.dashboard')->middleware('web');

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
// ADMIN STAFF MANAGEMENT & DELETED STAFF ROUTES
// ==========================================
Route::get('/admin/staff-management', function () {
    return response()
        ->view('dashboard')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
})->name('admin.staff-management')->middleware('web');

Route::get('/admin/deleted-staff', function () {
    return response()
        ->view('dashboard')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
})->name('admin.deleted-staff')->middleware('web');

// ==========================================
// EXISTING ROUTES (Your SPA & Profile)
// ==========================================
Route::get('/', function () {
    return no_cache_view('dashboard'); // Vue SPA entry
});

Route::get('/test', function () {
    return 'OK';
});

Route::middleware('auth')->group(function () {
    Route::post('/users/avatar', [ProfileController::class, 'uploadAvatar'])
        ->name('users.avatar');
});

// ==========================================
// TEST ROUTES (Development Only)
// ==========================================
Route::prefix('test')->group(function () {
    Route::get('/backend', function () {
        return response()->json([
            'status' => 'Backend is working!',
            'timestamp' => now()->toDateTimeString(),
            'laravel_version' => app()->version(),
        ]);
    });

    Route::get('/auth', function () {
        $user = \Illuminate\Support\Facades\Auth::user();

        if ($user) {
            return response()->json([
                'authenticated' => true,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'branch_id' => $user->branch_id,
                ],
            ]);
        }

        return response()->json([
            'authenticated' => false,
            'message' => 'No user logged in',
        ]);
    });

    Route::get('/manager-dashboard', function () {
        $testUser = \App\Models\User::where('role', 'BRANCH_MANAGER')->first();

        if (!$testUser) {
            return response()->json([
                'error' => 'No Branch Manager found in database',
                'hint' => 'Create a Branch Manager user first',
            ]);
        }

        return response()->json([
            'message' => 'Branch Manager found',
            'user' => [
                'id' => $testUser->id,
                'username' => $testUser->username,
                'role' => $testUser->role,
                'branch_id' => $testUser->branch_id,
            ],
            'api_endpoint' => '/api/manager/dashboard',
            'note' => 'Login as this user then access the API',
        ]);
    });

    Route::get('/staff-dashboard', function () {
        $testUser = \App\Models\User::where('role', 'STAFF')->first();

        if (!$testUser) {
            return response()->json([
                'error' => 'No Staff found in database',
                'hint' => 'Create a Staff user first',
            ]);
        }

        return response()->json([
            'message' => 'Staff found',
            'user' => [
                'id' => $testUser->id,
                'username' => $testUser->username,
                'role' => $testUser->role,
                'branch_id' => $testUser->branch_id,
            ],
            'api_endpoint' => '/api/staff/dashboard',
            'note' => 'Login as this user then access the API',
        ]);
    });

    Route::get('/routes', function () {
        $routes = [
            'manager_routes' => [
                'GET /api/manager/dashboard',
                'GET /api/manager/inventory',
                'GET /api/manager/staff',
                'GET /api/manager/reports/sales',
            ],
            'staff_routes' => [
                'GET /api/staff/dashboard',
                'POST /api/staff/clock-in',
                'POST /api/staff/clock-out',
                'GET /api/staff/attendance/status',
            ],
        ];

        return response()->json([
            'message' => 'Available API routes',
            'routes' => $routes,
            'note' => 'All routes require authentication',
        ]);
    });
});

// ==========================================
// SPA CATCH-ALL (MUST BE LAST!)
// ==========================================
Route::get('/{any}', function () {
    return no_cache_view('dashboard');
})->where('any', '.*');
