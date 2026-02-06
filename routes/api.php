<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Manager\ManagerDashboardController;
use App\Http\Controllers\Manager\InventoryController;
use App\Http\Controllers\Manager\StaffManagementController;
use App\Http\Controllers\Manager\ReportsController;
use App\Http\Controllers\Staff\StaffDashboardController;
use App\Http\Controllers\Staff\AttendanceController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\TimesheetController;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Models\User;  // ← ADDED: Import your User model
use App\Http\Controllers\Api\ProductCommentController;

// API routes using session (web guard)
Route::middleware('web')->group(function () {
    // ==========================================
    // CSRF TOKEN ENDPOINT
    // ==========================================
    Route::get('/csrf-token', function () {
        return response()->json(['token' => csrf_token()]);
    });

    // ==========================================
    // PRODUCT COMMENTS (PUBLIC)
    // ==========================================
    Route::get('/product-comments', [ProductCommentController::class, 'index']);
    Route::post('/product-comments', [ProductCommentController::class, 'store'])
        ->middleware('throttle:10,1');
    Route::post('/product-comment-replies', [ProductCommentController::class, 'storeReply'])
        ->middleware('throttle:10,1');

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
            'password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[!@#$%^&*])[A-Za-z\\d!@#$%^&*]{8,}$/',
            ],
        ]);


        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                // Save to password_hash column for compatibility
                $user->password_hash = Hash::make($password);
                $user->must_change_password = false;
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
    Route::get('/user/profile',     [AuthController::class, 'profile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/owner-profile',    [AuthController::class, 'ownerProfile']);
    Route::put('/owner-profile',    [AuthController::class, 'updateOwnerProfile']);
    Route::post('/upload-avatar',   [AuthController::class, 'uploadAvatar']);

    Route::get('/owner-dashboard', [OwnerDashboardController::class, 'index']);

    // ==========================================
    // STAFF MANAGEMENT API
    // ==========================================
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard',        [DashboardController::class, 'index']);
        Route::get('/staff',            [StaffController::class, 'apiIndex']);
        Route::get('/staff/{id}',       [StaffController::class, 'apiShow']);
        Route::post('/staff',           [StaffController::class, 'apiStore']);
        Route::put('/staff/{id}',       [StaffController::class, 'apiUpdate']);
        Route::delete('/staff/{id}',    [StaffController::class, 'apiDestroy']);
        Route::get('/branches',         [StaffController::class, 'apiBranches']);

        // Document routes
        Route::get('/staff/{id}/document/{documentType}', [StaffController::class, 'downloadDocument']);
        Route::delete('/staff/{id}/document/{documentType}', [StaffController::class, 'deleteDocument']);
        Route::post('/staff/{id}/document/{documentType}', [StaffController::class, 'uploadDocument']);
        // Admin attendance monitoring (owner/admin/HR)
        Route::get('/attendance',       [AdminAttendanceController::class, 'index'])
            ->withoutMiddleware([VerifyCsrfToken::class]);
    });

    // ==========================================
    // BRANCH MANAGER API
    // ==========================================
    Route::prefix('manager')->group(function () {
        // Dashboard
        Route::get('/dashboard',        [ManagerDashboardController::class, 'index']);

        // Inventory Management
        Route::get('/inventory',        [InventoryController::class, 'index']);
        Route::put('/inventory/{id}',   [InventoryController::class, 'updateStock']);
        Route::post('/inventory/delivery', [InventoryController::class, 'recordDelivery']);

        // Staff Management
        Route::get('/staff',            [StaffManagementController::class, 'index']);
        Route::post('/staff',           [StaffManagementController::class, 'store']);
        Route::put('/staff/{id}',       [StaffManagementController::class, 'update']);
        Route::get('/staff/schedules',  [StaffManagementController::class, 'schedules']);
        Route::get('/staff/attendance', [StaffManagementController::class, 'attendance']);

        // Reports
        Route::get('/reports/sales',    [ReportsController::class, 'salesReport']);
        Route::get('/reports/staff-performance', [ReportsController::class, 'staffPerformanceReport']);
        Route::get('/reports/inventory', [ReportsController::class, 'inventoryReport']);
        Route::get('/reports/export',   [ReportsController::class, 'exportCSV']);
    });

    // ==========================================
    // STAFF API
    // ==========================================
    Route::prefix('staff')->group(function () {
        // Dashboard
        Route::get('/dashboard',        [StaffDashboardController::class, 'index']);

        // Attendance/Clock In-Out
        Route::post('/clock-in',        [AttendanceController::class, 'clockIn'])
            ->withoutMiddleware([VerifyCsrfToken::class]);
        Route::post('/clock-out',       [AttendanceController::class, 'clockOut'])
            ->withoutMiddleware([VerifyCsrfToken::class]);
        Route::get('/attendance/status', [AttendanceController::class, 'status'])
            ->withoutMiddleware([VerifyCsrfToken::class]);
        Route::get('/attendance/history', [AttendanceController::class, 'history'])
            ->withoutMiddleware([VerifyCsrfToken::class]);
        Route::get('/attendance/branch', [AttendanceController::class, 'getBranchAttendance'])
            ->withoutMiddleware([VerifyCsrfToken::class]);
    });

    });
