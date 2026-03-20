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

    // Super Admin Profile endpoints
    Route::get('/superadmin-profile', [\App\Http\Controllers\Api\SuperAdminController::class, 'profile']);
    Route::put('/superadmin-profile', [\App\Http\Controllers\Api\SuperAdminController::class, 'updateProfile']);
    Route::post('/superadmin/avatar', [\App\Http\Controllers\Api\SuperAdminController::class, 'uploadAvatar']);
    Route::get('/superadmin/dashboard', [\App\Http\Controllers\Api\SuperAdminController::class, 'dashboard']);
    Route::get('/superadmin/all-staff', [\App\Http\Controllers\Api\SuperAdminController::class, 'allStaff']);
Route::post('/superadmin/announce', [\App\Http\Controllers\Api\SuperAdminController::class, 'sendAnnouncement']);
    Route::post('/superadmin/terms', [\App\Http\Controllers\Api\SuperAdminController::class, 'updateTerms']);
    // Public announcements endpoint for authenticated users
    Route::get('/announcements', [\App\Http\Controllers\Api\AnnouncementController::class, 'index']);

// SuperAdmin Logistics - Product Management across all branches
    Route::get('/superadmin/logistics/products', [\App\Http\Controllers\Api\SuperAdminController::class, 'logisticsProducts']);
    Route::post('/superadmin/logistics/products', [\App\Http\Controllers\Api\SuperAdminController::class, 'logisticsStoreProduct']);
    Route::put('/superadmin/logistics/products/{id}', [\App\Http\Controllers\Api\SuperAdminController::class, 'logisticsUpdateProduct']);
    Route::delete('/superadmin/logistics/products/{id}', [\App\Http\Controllers\Api\SuperAdminController::class, 'logisticsDestroyProduct']);
    Route::get('/superadmin/logistics/branches', [\App\Http\Controllers\Api\SuperAdminController::class, 'logisticsBranches']);

    // SuperAdmin Branch Management
    Route::get('/superadmin/branches', [\App\Http\Controllers\Api\SuperAdminController::class, 'branchesWithAccounts']);
    Route::post('/superadmin/branches', [\App\Http\Controllers\Api\SuperAdminController::class, 'storeBranch']);
    // Delete a branch (soft-delete branch and associated user accounts)
    Route::delete('/superadmin/branches/{id}', [\App\Http\Controllers\Api\SuperAdminController::class, 'deleteBranch']);

    // ==========================================
    // SUPERADMIN FINANCE MODULE
    // Financial monitoring, analytics, and reporting across all branches
    // ==========================================
    Route::prefix('superadmin/finance')->group(function () {
        // Dashboard - Get financial KPIs
        Route::get('/dashboard', [\App\Http\Controllers\SuperAdmin\Finance\SuperAdminFinanceController::class, 'dashboard']);

        // Branch Financial Performance
        Route::get('/branches', [\App\Http\Controllers\SuperAdmin\Finance\SuperAdminFinanceController::class, 'branches']);

        // Transaction Monitoring
        Route::get('/transactions', [\App\Http\Controllers\SuperAdmin\Finance\SuperAdminTransactionController::class, 'index']);
        Route::get('/transactions/{id}', [\App\Http\Controllers\SuperAdmin\Finance\SuperAdminTransactionController::class, 'show']);

        // Expense Monitoring (placeholder - no expenses table yet)
        Route::get('/expenses', [\App\Http\Controllers\SuperAdmin\Finance\SuperAdminExpenseController::class, 'index']);
        Route::get('/expenses/summary', [\App\Http\Controllers\SuperAdmin\Finance\SuperAdminExpenseController::class, 'summary']);

        // Refund Monitoring
        Route::get('/refunds', [\App\Http\Controllers\SuperAdmin\Finance\SuperAdminRefundController::class, 'index']);
        Route::get('/refunds/summary', [\App\Http\Controllers\SuperAdmin\Finance\SuperAdminRefundController::class, 'summary']);

        // Settlement Monitoring (placeholder - no settlements table yet)
        Route::get('/settlements', [\App\Http\Controllers\SuperAdmin\Finance\SuperAdminSettlementController::class, 'index']);
        Route::get('/settlements/summary', [\App\Http\Controllers\SuperAdmin\Finance\SuperAdminSettlementController::class, 'summary']);

        // Finance Reports
        Route::get('/reports', [\App\Http\Controllers\SuperAdmin\Finance\SuperAdminReportController::class, 'index']);
        Route::get('/export', [\App\Http\Controllers\SuperAdmin\Finance\SuperAdminReportController::class, 'export']);
    });

    // ==========================================
    // SUPERADMIN CASHIER
    // ==========================================
    Route::get('/superadmin/cashier/branches',    [\App\Http\Controllers\Api\CashierController::class, 'branches']);
    Route::get('/superadmin/cashier/products',    [\App\Http\Controllers\Api\CashierController::class, 'products']);
    Route::post('/superadmin/cashier/checkout',   [\App\Http\Controllers\Api\CashierController::class, 'checkout']);
    Route::post('/superadmin/cashier/cancel-pending', [\App\Http\Controllers\Api\CashierController::class, 'cancelPending']);
    Route::get('/superadmin/cashier/transactions',[\App\Http\Controllers\Api\CashierController::class, 'transactions']);

    Route::get('/owner-dashboard', [OwnerDashboardController::class, 'index']);

    // HR Messaging routes
    Route::prefix('hr')->middleware('auth')->group(function () {
        Route::get('/messages/users', [\App\Http\Controllers\HRMessageController::class, 'users']);
        Route::get('/messages/conversation/{userId}', [\App\Http\Controllers\HRMessageController::class, 'conversation']);
        Route::post('/messages/send', [\App\Http\Controllers\HRMessageController::class, 'send']);
    });

    // ==========================================
    // STAFF MANAGEMENT API
    // ==========================================
    Route::prefix('admin')->middleware('auth')->group(function () {
        Route::get('/dashboard',        [DashboardController::class, 'index']);
        Route::get('/staff',            [StaffController::class, 'apiIndex']);
        Route::get('/staff/{id}',       [StaffController::class, 'apiShow']);
        Route::post('/staff',           [StaffController::class, 'apiStore']);
        Route::put('/staff/{id}',       [StaffController::class, 'apiUpdate']);
        Route::delete('/staff/{id}',    [StaffController::class, 'apiDestroy']);
        Route::post('/staff/{id}/reset-password', [StaffController::class, 'resetPassword']);
        Route::get('/branches',         [StaffController::class, 'apiBranches']);
        Route::get('/attendance', [\App\Http\Controllers\Admin\AttendanceController::class, 'index']);

        // Config endpoint for default password (requires authentication)
        Route::get('/config/default-password', [ConfigController::class, 'defaultPassword']);
    });

    // ==========================================
    // BRANCH MANAGER API - Protected with auth middleware
    // All manager routes require authentication
    // Using 'auth' middleware (web guard) which works with both session and token
    // ==========================================
Route::middleware('auth')->group(function () {
    Route::apiResource('procurement-requests', \App\Http\Controllers\Api\ProcurementRequestController::class)->except(['show']);
    Route::get('procurement-requests/requested-products', [\App\Http\Controllers\Api\ProcurementRequestController::class, 'requestedProducts']);
    Route::post('procurement-requests/{id}/status', [\App\Http\Controllers\Api\ProcurementRequestController::class, 'updateStatus']);
    Route::post('procurement-requests/{id}/complete', [\App\Http\Controllers\Api\ProcurementRequestController::class, 'completeOrder']);

    Route::apiResource('procurement.products', \App\Http\Controllers\Api\ProcurementProductController::class)->only(['index']);
    Route::post('procurement.products/{productId}/place-order', [\App\Http\Controllers\Api\ProcurementProductController::class, 'placeOrder']);

    Route::apiResource('supplier-orders', \App\Http\Controllers\Api\SupplierOrderController::class)->only(['index']);
    Route::put('supplier-orders/{id}/status', [\App\Http\Controllers\Api\SupplierOrderController::class, 'updateStatus']);
});

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
        Route::get('/logistics/products', [\App\Http\Controllers\Api\ManagerProfileController::class, 'logisticsProducts']);
        Route::get('/logistics/suppliers', [\App\Http\Controllers\Api\ManagerProfileController::class, 'logisticsSuppliers']);

        // Procurement Manager endpoints
        Route::get('/procurement/profile', [\App\Http\Controllers\Api\ManagerProfileController::class, 'procurementProfile']);
        Route::put('/procurement/profile', [\App\Http\Controllers\Api\ManagerProfileController::class, 'updateProcurementProfile']);
        Route::get('/procurement/dashboard', [\App\Http\Controllers\Api\ManagerProfileController::class, 'procurementDashboard']);
        Route::get('/procurement/products', [\App\Http\Controllers\Api\ManagerProfileController::class, 'procurementProducts']);
        Route::post('/procurement/products/{id}/place-order', [\App\Http\Controllers\Api\ManagerProfileController::class, 'placeOrderProduct']);
        // Procurement supplier management
        Route::post('/procurement/suppliers', [\App\Http\Controllers\Api\ManagerProfileController::class, 'createProcurementSupplier']);

        // Budget Request System - Logistics Manager (Read-only inventory + Budget requests)
        Route::get('/logistics/inventory', [\App\Http\Controllers\Api\ManagerProfileController::class, 'logisticsInventory']);
        Route::get('/logistics/budget/my-requests', [\App\Http\Controllers\Manager\BudgetRequestController::class, 'getMyRequests']);
        Route::post('/logistics/budget/create', [\App\Http\Controllers\Manager\BudgetRequestController::class, 'createRequest']);

        // Also expose procurement endpoints for budget requests (procurement managers)
        Route::get('/procurement/budget/my-requests', [\App\Http\Controllers\Manager\BudgetRequestController::class, 'getMyRequests']);
        Route::post('/procurement/budget/create', [\App\Http\Controllers\Manager\BudgetRequestController::class, 'createRequest']);

        // Budget Request System - Finance Manager (Approval/Rejection)
        Route::get('/finance/budget/all', [\App\Http\Controllers\Manager\BudgetRequestController::class, 'getAllRequests']);
        Route::put('/finance/budget/{id}/approve', [\App\Http\Controllers\Manager\BudgetRequestController::class, 'approveRequest']);
        Route::put('/finance/budget/{id}/reject', [\App\Http\Controllers\Manager\BudgetRequestController::class, 'rejectRequest']);
        // Mark budget as handed to procurement (finance confirms physical handover)
        Route::put('/finance/budget/{id}/given', [\App\Http\Controllers\Manager\BudgetRequestController::class, 'markGiven']);

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

            // Backwards-compatible aliases (old frontend used these paths)
            Route::post('/inventory/store', [\App\Http\Controllers\Staff\StaffInventoryController::class, 'store']);
            Route::put('/inventory/update/{id}', [\App\Http\Controllers\Staff\StaffInventoryController::class, 'update']);
            Route::delete('/inventory/destroy/{id}', [\App\Http\Controllers\Staff\StaffInventoryController::class, 'destroy']);

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
            Route::get('/finance/logs', [\App\Http\Controllers\Staff\StaffFinanceController::class, 'logs']);

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
    Route::post('/auth/verify-code', [AuthController::class, 'verifyCode']);
    Route::post('/auth/confirm-email', [AuthController::class, 'confirmEmail'])->middleware('auth');
    Route::post('/auth/register', [AuthController::class, 'registerPublic']);
    Route::post('/auth/login', [AuthController::class, 'loginPublic']);

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
