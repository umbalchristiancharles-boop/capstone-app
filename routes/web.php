<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\AdminPasswordResetController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\DeletedStaffController;
use App\Http\Controllers\HRMessageController;

// ==========================================
// AUTHENTICATION ROUTES (Login/Logout)
// ==========================================

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
})->name('admin.dashboard')->middleware(['web', 'auth']);

// Super Admin Panel - full system access
Route::get('/super-admin-panel', function () {
    return response()
        ->view('dashboard')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
})->name('superadmin.dashboard')->middleware(['web', 'auth']);

// Main Branch (Admin) Branches page - moved from owner to main-branch admin panel
Route::get('/main-branch/branches', function () {
    return response()
        ->view('dashboard')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
})->name('mainbranch.branches')->middleware(['web', 'auth']);

// Main Branch Finance Manager Panel
Route::get('/main-branch/finance', function () {
    return response()
        ->view('dashboard')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
})->name('mainbranch.finance')->middleware(['web', 'auth']);

// Main Branch Logistics Manager Panel
Route::get('/main-branch/logistics', function () {
    return response()
        ->view('dashboard')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
})->name('mainbranch.logistics')->middleware(['web', 'auth']);

// Main Branch HR Manager Panel
Route::get('/main-branch/hr', function () {
    return response()
        ->view('dashboard')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
})->name('mainbranch.hr')->middleware(['web', 'auth']);

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
Route::get('/staff-management', function () {
    return response()
        ->view('dashboard')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
})->name('admin.staff-management')->middleware(['web', 'auth']);

// OWNER STAFF MANAGEMENT ROUTE (for OwnerStaffManagement.vue)
Route::get('/owner/staff-management', function () {
    return response()
        ->view('dashboard')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
})->name('owner.staff-management')->middleware(['web', 'auth', \App\Http\Middleware\OwnerOnly::class]);

// OWNER PRICE MARKUP APPROVALS ROUTE (for OwnerPriceMarkupPanel.vue)
Route::get('/owner/price-markup-approvals', function () {
    return response()
        ->view('dashboard')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
})->name('owner.price-markup-approvals')->middleware(['web', 'auth', \App\Http\Middleware\OwnerOnly::class]);

// OWNER BRANCH CONFIRMATIONS ROUTE (for OwnerBranchConfirmations.vue)
Route::get('/owner/branch-confirmations', function () {
    return response()
        ->view('dashboard')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
})->name('owner.branch-confirmations')->middleware(['web', 'auth', \App\Http\Middleware\OwnerOnly::class]);

// MANAGER STAFF MANAGEMENT
Route::get('/manager/staff', function () {
    return response()
        ->view('dashboard')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
})->name('manager.staff-management')->middleware(['web', 'auth']);

Route::get('/admin/deleted-staff', function () {
    return response()
        ->view('dashboard')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
})->name('admin.deleted-staff')->middleware(['web', 'auth']);

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
});

// ==========================================
// DEBUG ROUTES (Development Only)
// ==========================================
Route::prefix('debug')->group(function () {
    // Check products status for comments
    Route::get('/products-status', function () {
        $total = \App\Models\Product::count();
        $active = \App\Models\Product::where('is_active', 1)->count();
        $published = \App\Models\Product::where('is_published', 1)->count();
        $activeAndPublished = \App\Models\Product::where('is_active', 1)->where('is_published', 1)->count();

        $publishedProducts = \App\Models\Product::where('is_active', 1)
            ->where('is_published', 1)
            ->select('id', 'name', 'is_active', 'is_published')
            ->limit(20)
            ->get();

        return response()->json([
            'summary' => [
                'total' => $total,
                'active' => $active,
                'published' => $published,
                'active_and_published' => $activeAndPublished,
            ],
            'published_products' => $publishedProducts,
        ]);
    });

    // Check comments
    Route::get('/comments-count', function () {
        $total = \App\Models\ProductComment::count();
        $latestComments = \App\Models\ProductComment::with('product')
            ->latest('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'total' => $total,
            'latest' => $latestComments,
        ]);
    });

    // Test comment validation
    Route::post('/test-comment-validation', function (\Illuminate\Http\Request $request) {
        $productId = $request->input('product_id');
        
        $product = \App\Models\Product::find($productId);
        $exists = \App\Models\Product::where('id', $productId)->where('is_active', 1)->where('is_published', 1)->exists();
        
        return response()->json([
            'product_id' => $productId,
            'exists_in_db' => $product ? true : false,
            'product_data' => $product ? [
                'name' => $product->name,
                'is_active' => $product->is_active,
                'is_published' => $product->is_published,
            ] : null,
            'validation_pass' => $exists,
            'notes' => 'For validation to pass, product must have is_active=1 AND is_published=1'
        ]);
    });
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

/* HR messaging routes removed - use /api/hr/* instead */

// ==========================================
// SPA CATCH-ALL (MUST BE LAST!)
// ==========================================
Route::get('/{any}', function () {
    return no_cache_view('dashboard');
})->where('any', '.*');
