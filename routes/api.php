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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Models\User;
use App\Http\Controllers\Api\ProductCommentController;
use App\Http\Controllers\Api\ConfigController;

// API routes using session (web guard)
Route::middleware('web')->group(function () {
    // ==========================================
    // AUTH & PROFILE ROUTES
    // ==========================================
    Route::post('/login',           [AuthController::class, 'login']);
    Route::post('/logout',          [AuthController::class, 'logout']);
    // Add missing password change route for modal (require session auth)
    Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth');

    // FIXED: Forgot Password Routes (no auth needed)
    Route::post('/forgot-password', function (Request $request) {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
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
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();
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
        Route::get('/dashboard',        [DashboardController::class, 'index']);
        Route::get('/staff',            [StaffController::class, 'apiIndex']);
        Route::get('/staff/{id}',       [StaffController::class, 'apiShow']);
        Route::post('/staff',           [StaffController::class, 'apiStore']);
        Route::put('/staff/{id}',       [StaffController::class, 'apiUpdate']);
        Route::delete('/staff/{id}',    [StaffController::class, 'apiDestroy']);
        Route::get('/branches',         [StaffController::class, 'apiBranches']);
        Route::get('/attendance', [\App\Http\Controllers\Admin\AttendanceController::class, 'index']);

        // Config endpoint for default password
        Route::get('/config/default-password', [ConfigController::class, 'defaultPassword']);
    });

    // ==========================================
    // BRANCH MANAGER API - Protected with auth middleware
    // All manager routes require authentication
    // Using 'auth' middleware (web guard) which works with both session and token
    // ==========================================
    Route::prefix('manager')->middleware('auth')->group(function () {
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

        // Attendance - Manager can clock in/out
        Route::post('/clock-in',        [AttendanceController::class, 'clockIn']);
        Route::post('/clock-out',       [AttendanceController::class, 'clockOut']);
        Route::get('/attendance/status', [AttendanceController::class, 'status']);
        Route::get('/attendance/history', [AttendanceController::class, 'history']);

        // Manager Profile Endpoints (for HR, Finance, Logistics, Inventory departments)
        Route::get('/hr/profile', [\App\Http\Controllers\Api\ManagerProfileController::class, 'hrProfile']);
        Route::put('/hr/profile', [\App\Http\Controllers\Api\ManagerProfileController::class, 'updateHrProfile']);
        Route::get('/hr/dashboard', [\App\Http\Controllers\Api\ManagerProfileController::class, 'hrDashboard']);
        Route::get('/hr/staff', [\App\Http\Controllers\Api\ManagerProfileController::class, 'hrStaff']);
        Route::post('/hr/staff', [\App\Http\Controllers\Api\ManagerProfileController::class, 'createHrStaff']);
        Route::put('/hr/staff/{id}', [\App\Http\Controllers\Api\ManagerProfileController::class, 'updateHrStaff']);
        Route::delete('/hr/staff/{id}', [\App\Http\Controllers\Api\ManagerProfileController::class, 'deleteHrStaff']);
        Route::get('/hr/reports', [\App\Http\Controllers\Api\ManagerProfileController::class, 'hrReports']);

        Route::get('/finance/profile', [\App\Http\Controllers\Api\ManagerProfileController::class, 'financeProfile']);
        Route::put('/finance/profile', [\App\Http\Controllers\Api\ManagerProfileController::class, 'updateFinanceProfile']);
        Route::get('/finance/dashboard', [\App\Http\Controllers\Api\ManagerProfileController::class, 'financeDashboard']);
        Route::get('/finance/reports', [\App\Http\Controllers\Api\ManagerProfileController::class, 'financeReports']);
        Route::get('/finance/transactions', [\App\Http\Controllers\Api\ManagerProfileController::class, 'financeTransactions']);

        Route::get('/logistics/profile', [\App\Http\Controllers\Api\ManagerProfileController::class, 'logisticsProfile']);
        Route::put('/logistics/profile', [\App\Http\Controllers\Api\ManagerProfileController::class, 'updateLogisticsProfile']);
        Route::get('/logistics/dashboard', [\App\Http\Controllers\Api\ManagerProfileController::class, 'logisticsDashboard']);
        Route::get('/logistics/deliveries', [\App\Http\Controllers\Api\ManagerProfileController::class, 'logisticsDeliveries']);
        Route::get('/logistics/suppliers', [\App\Http\Controllers\Api\ManagerProfileController::class, 'logisticsSuppliers']);

        Route::get('/inventory/profile', [\App\Http\Controllers\Api\ManagerProfileController::class, 'invProfile']);
        Route::put('/inventory/profile', [\App\Http\Controllers\Api\ManagerProfileController::class, 'updateInvProfile']);
        Route::get('/inventory/dashboard', [\App\Http\Controllers\Api\ManagerProfileController::class, 'invDashboard']);
        Route::get('/inventory/products', [\App\Http\Controllers\Api\ManagerProfileController::class, 'invProducts']);
        Route::get('/inventory/reports', [\App\Http\Controllers\Api\ManagerProfileController::class, 'invReports']);
    });

    // ==========================================
    // STAFF API - Protected routes requiring authentication
    // ==========================================
    Route::prefix('staff')->group(function () {
        // Apply auth middleware to all staff routes
        Route::middleware(['auth'])->group(function () {
            // Dashboard
            Route::get('/dashboard',        [StaffDashboardController::class, 'index']);

            // Attendance/Clock In-Out - works for all roles (Staff, Manager, Owner)
            Route::post('/clock-in',        [AttendanceController::class, 'clockIn']);
            Route::post('/clock-out',       [AttendanceController::class, 'clockOut']);
            Route::get('/attendance/status', [AttendanceController::class, 'status']);
            Route::get('/attendance/history', [AttendanceController::class, 'history']);

            // Staff Inventory - Products
            Route::get('/inventory/products', [\App\Http\Controllers\Staff\StaffInventoryController::class, 'index']);
            Route::post('/inventory/products', [\App\Http\Controllers\Staff\StaffInventoryController::class, 'store']);
            Route::put('/inventory/products/{id}', [\App\Http\Controllers\Staff\StaffInventoryController::class, 'update']);
            Route::delete('/inventory/products/{id}', [\App\Http\Controllers\Staff\StaffInventoryController::class, 'destroy']);

            // Staff Inventory - Profile
            Route::get('/inventory/profile', [\App\Http\Controllers\Staff\StaffInventoryController::class, 'profile']);
            Route::put('/inventory/profile', [\App\Http\Controllers\Staff\StaffInventoryController::class, 'updateProfile']);
            Route::post('/inventory/avatar', [\App\Http\Controllers\Staff\StaffInventoryController::class, 'uploadAvatar']);

            // Staff Profile - Generic endpoints for all staff roles
            Route::get('/profile', [\App\Http\Controllers\Staff\StaffProfileController::class, 'profile']);
            Route::put('/profile', [\App\Http\Controllers\Staff\StaffProfileController::class, 'updateProfile']);
            Route::post('/avatar', [\App\Http\Controllers\Staff\StaffProfileController::class, 'uploadAvatar']);

            // Staff Finance - Finance staff endpoints
            Route::get('/finance/profile', [\App\Http\Controllers\Staff\StaffProfileController::class, 'profile']);
            Route::put('/finance/profile', [\App\Http\Controllers\Staff\StaffProfileController::class, 'updateProfile']);
            Route::get('/finance/logs', function() { return response()->json([]); });

            // Staff Cashier - Cashier staff endpoints
            Route::get('/cashier/profile', [\App\Http\Controllers\Staff\StaffProfileController::class, 'profile']);
            Route::put('/cashier/profile', [\App\Http\Controllers\Staff\StaffProfileController::class, 'updateProfile']);
            Route::get('/cashier/transactions', function() { return response()->json([]); });

            // Staff Logistics - Logistics staff endpoints
            Route::get('/logistics/profile', [\App\Http\Controllers\Staff\StaffProfileController::class, 'profile']);
            Route::put('/logistics/profile', [\App\Http\Controllers\Staff\StaffProfileController::class, 'updateProfile']);
            Route::get('/logistics/deliveries', function() { return response()->json([]); });
        });
    });

    // ==========================================
    // OWNER/ADMIN API - Attendance routes for Owner and Admin
    // ==========================================
    Route::prefix('owner')->group(function () {
        Route::middleware(['auth'])->group(function () {
            // Attendance - Owner can clock in/out just like staff
            Route::post('/clock-in',        [AttendanceController::class, 'clockIn']);
            Route::post('/clock-out',       [AttendanceController::class, 'clockOut']);
            Route::get('/attendance/status', [AttendanceController::class, 'status']);
            Route::get('/attendance/history', [AttendanceController::class, 'history']);
        });
    });

    // PRODUCT COMMENTS API
    Route::get('/product-comments', [ProductCommentController::class, 'index']);
    Route::post('/product-comments', [ProductCommentController::class, 'store']);
    Route::post('/product-comment-replies', [ProductCommentController::class, 'storeReply']);

    Route::post('/auth/send-verification', [AuthController::class, 'sendVerification']);

    // ==========================================
    // ATTENDANCE SETTINGS API
    // ==========================================
    Route::prefix('attendance')->middleware('auth')->group(function () {
        // Get attendance settings for user's branch
        Route::get('/settings', [\App\Http\Controllers\Staff\AttendanceSettingsController::class, 'getSettings']);

        // Toggle early clock-out override (OWNER/HR only)
        Route::patch('/override', [\App\Http\Controllers\Staff\AttendanceSettingsController::class, 'toggleOverride']);

        // Update attendance settings (OWNER/HR only)
        Route::put('/settings', [\App\Http\Controllers\Staff\AttendanceSettingsController::class, 'updateSettings']);
    });
});
