<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JwtAuthController;
use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminFinanceController;
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
use App\Http\Controllers\Api\PanelNotificationController;
use App\Http\Controllers\Api\LocationController;

// ==========================================
// JWT AUTHENTICATION ROUTES (Cross-Domain)
// ==========================================
Route::post('/jwt/login', [JwtAuthController::class, 'login']);
Route::post('/jwt/refresh', [JwtAuthController::class, 'refresh']);
Route::post('/jwt/logout', [JwtAuthController::class, 'logout']);
Route::post('/jwt/logout-all', [JwtAuthController::class, 'logoutAll'])->middleware('jwt_token');
Route::get('/jwt/me', [JwtAuthController::class, 'me'])->middleware('jwt_token');

// API routes using session (web guard)
Route::middleware('web')->group(function () {
    // ==========================================
    // AUTH & PROFILE ROUTES
    // ==========================================
    Route::post('/login',           [AuthController::class, 'login']);
    Route::post('/logout',          [AuthController::class, 'logout']);
    // Add missing password change route for modal (require session auth)
    Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth');

    // Account setup routes (for new accounts)
    Route::put('/auth/setup/account-info', [AuthController::class, 'updateAccountSetup'])->middleware('auth');
    Route::post('/auth/setup/document/{documentType}', [AuthController::class, 'uploadSetupDocument'])->middleware('auth');
    Route::get('/auth/setup/status', [AuthController::class, 'getSetupStatus'])->middleware('auth');

    // Temporary debug endpoint - returns auth and header/cookie info for troubleshooting
    Route::get('/debug/auth-check', function (\Illuminate\Http\Request $request) {
        $user = null;
        try { $user = \Illuminate\Support\Facades\Auth::user(); } catch (\Throwable $_) { }
        $u = null;
        if ($user) {
            $u = [
                'id' => $user->id ?? null,
                'username' => $user->username ?? null,
                'role' => $user->role ?? null,
                'department' => $user->department ?? null,
                'branch_id' => $user->branch_id ?? null,
                'permissions' => $user->permissions ?? null,
            ];
        }
        return response()->json([
            'auth_check' => \Illuminate\Support\Facades\Auth::check(),
            'user' => $u,
            'bearer' => $request->bearerToken(),
            'headers' => $request->headers->all(),
            'cookies' => $request->cookies->all(),
        ]);
    });

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

    Route::get('/me',               [AuthController::class, 'me'])->middleware('auth');
    Route::get('/panel-descriptions', [ConfigController::class, 'panelDescriptions'])->middleware('auth');
    Route::get('/panel-notifications', [PanelNotificationController::class, 'index'])->middleware('auth');
    Route::get('/owner-profile',    [AuthController::class, 'ownerProfile'])->middleware('auth');
    Route::put('/owner-profile',    [AuthController::class, 'updateOwnerProfile'])->middleware('auth');
    Route::post('/upload-avatar',   [AuthController::class, 'uploadAvatar'])->middleware('auth');

    // Super Admin Profile endpoints
    Route::get('/superadmin-profile', [\App\Http\Controllers\Api\SuperAdminController::class, 'profile'])->middleware('auth');
    Route::put('/superadmin-profile', [\App\Http\Controllers\Api\SuperAdminController::class, 'updateProfile'])->middleware('auth');
    Route::post('/superadmin/avatar', [\App\Http\Controllers\Api\SuperAdminController::class, 'uploadAvatar'])->middleware('auth');
    Route::get('/superadmin/dashboard', [\App\Http\Controllers\Api\SuperAdminController::class, 'dashboard'])->middleware('auth');
    Route::get('/superadmin/all-staff', [\App\Http\Controllers\Api\SuperAdminController::class, 'allStaff'])->middleware('auth');
    Route::put('/superadmin/staff/{id}', [\App\Http\Controllers\Api\SuperAdminController::class, 'updateStaff'])->middleware('auth');
    Route::post('/superadmin/announce', [\App\Http\Controllers\Api\SuperAdminController::class, 'sendAnnouncement'])->middleware('auth');
    Route::post('/superadmin/terms', [\App\Http\Controllers\Api\SuperAdminController::class, 'updateTerms'])->middleware('auth');
    // Public announcements endpoint for authenticated users
    Route::get('/announcements', [\App\Http\Controllers\Api\AnnouncementController::class, 'index'])->middleware('auth');

// SuperAdmin Logistics - Product Management across all branches
    Route::get('/superadmin/logistics/products', [\App\Http\Controllers\Api\SuperAdminController::class, 'logisticsProducts'])->middleware(['auth','permission:logistics']);
    Route::post('/superadmin/logistics/products', [\App\Http\Controllers\Api\SuperAdminController::class, 'logisticsStoreProduct'])->middleware(['auth','permission:logistics']);
    Route::put('/superadmin/logistics/products/{id}', [\App\Http\Controllers\Api\SuperAdminController::class, 'logisticsUpdateProduct'])->middleware(['auth','permission:logistics']);
    Route::delete('/superadmin/logistics/products/{id}', [\App\Http\Controllers\Api\SuperAdminController::class, 'logisticsDestroyProduct'])->middleware(['auth','permission:logistics']);
    // Supplier orders accessible to SuperAdmin logistics viewers
    Route::get('/superadmin/logistics/supplier-orders', [\App\Http\Controllers\Api\SuperAdminController::class, 'logisticsSupplierOrders'])->middleware(['auth','permission:logistics']);
    Route::get('/superadmin/logistics/branches', [\App\Http\Controllers\Api\SuperAdminController::class, 'logisticsBranches'])->middleware(['auth','permission:logistics']);

    // SuperAdmin Supplier Management - Comprehensive supplier data monitoring and validation
    Route::prefix('superadmin/suppliers')->middleware(['auth','permission:admin'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\SuperAdminController::class, 'suppliers']);
        Route::get('audit/logs', [\App\Http\Controllers\Api\SuperAdminController::class, 'supplierAuditLogs']);
        Route::get('{id}', [\App\Http\Controllers\Api\SuperAdminController::class, 'supplierDetail']);
        Route::get('{id}/validate', [\App\Http\Controllers\Api\SuperAdminController::class, 'validateSupplier']);
        Route::get('{id}/duplicates', [\App\Http\Controllers\Api\SuperAdminController::class, 'checkDuplicates']);
        Route::get('{id}/activity', [\App\Http\Controllers\Api\SuperAdminController::class, 'supplierActivityHistory']);
        Route::put('{id}/status', [\App\Http\Controllers\Api\SuperAdminController::class, 'updateSupplierStatus']);
    });

    // SuperAdmin Logistics Monitoring - Real-time tracking and monitoring
    Route::prefix('superadmin/logistics')->middleware('auth')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\LogisticsMonitoringController::class, 'dashboard']);
        Route::get('/transactions', [\App\Http\Controllers\Api\LogisticsMonitoringController::class, 'transactions']);
        Route::get('/deliveries', [\App\Http\Controllers\Api\SuperAdminController::class, 'logisticsDeliveries']);
        Route::get('/pending-verification', [\App\Http\Controllers\Api\LogisticsMonitoringController::class, 'pendingVerification']);
        Route::get('/variances', [\App\Http\Controllers\Api\LogisticsMonitoringController::class, 'variances']);
        Route::get('/report', [\App\Http\Controllers\Api\LogisticsMonitoringController::class, 'report']);
        Route::post('/transactions/{id}/update-status', [\App\Http\Controllers\Api\LogisticsMonitoringController::class, 'updateStatus']);
    });

    // SuperAdmin Branch Management
    Route::get('/superadmin/branches', [\App\Http\Controllers\Api\SuperAdminController::class, 'branchesWithAccounts'])->middleware(['auth', 'permission:admin']);
    Route::post('/superadmin/branches', [\App\Http\Controllers\Api\SuperAdminController::class, 'storeBranch'])->middleware(['auth', 'permission:admin,admin.branches']);
    // Delete a branch (soft-delete branch and associated user accounts)
    Route::delete('/superadmin/branches/{id}', [\App\Http\Controllers\Api\SuperAdminController::class, 'deleteBranch'])->middleware(['auth', 'permission:admin,admin.branches']);
    // Deactivate a branch (prevent login for accounts in that branch)
    Route::patch('/superadmin/branches/{id}/deactivate', [\App\Http\Controllers\Api\SuperAdminController::class, 'deactivateBranch'])->middleware(['auth', 'permission:admin,admin.branches']);
    // Reactivate a branch (allow login for accounts in that branch)
    Route::patch('/superadmin/branches/{id}/reactivate', [\App\Http\Controllers\Api\SuperAdminController::class, 'reactivateBranch'])->middleware(['auth', 'permission:admin,admin.branches']);

    // Owner Branch Approval
    Route::get('/owner/branch-requests', [\App\Http\Controllers\Api\SuperAdminController::class, 'pendingBranchRequests'])->middleware(['auth']);
    Route::post('/owner/branch-requests/{id}/approve', [\App\Http\Controllers\Api\SuperAdminController::class, 'approveBranchRequest'])->middleware(['auth']);
    Route::post('/owner/branch-requests/{id}/reject', [\App\Http\Controllers\Api\SuperAdminController::class, 'rejectBranchRequest'])->middleware(['auth']);

    // Main Branch Finance Branch Confirmation
    Route::get('/main-branch/finance/branch-requests', [\App\Http\Controllers\Api\SuperAdminController::class, 'pendingFinanceBranchRequests'])->middleware(['auth']);
    Route::post('/main-branch/finance/branch-requests/{id}/approve', [\App\Http\Controllers\Api\SuperAdminController::class, 'approveFinanceBranchRequest'])->middleware(['auth']);
    Route::post('/main-branch/finance/branch-requests/{id}/reject', [\App\Http\Controllers\Api\SuperAdminController::class, 'rejectFinanceBranchRequest'])->middleware(['auth']);

    // ==========================================
    // SUPERADMIN FINANCE MODULE
    // Financial monitoring, analytics, and reporting across all branches
    // ==========================================
    Route::prefix('superadmin/finance')->middleware(['auth','permission:finance'])->group(function () {
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
    Route::get('/superadmin/cashier/branches',    [\App\Http\Controllers\Api\CashierController::class, 'branches'])->middleware(['auth','permission:cashier']);
    Route::get('/superadmin/cashier/products',    [\App\Http\Controllers\Api\CashierController::class, 'products'])->middleware(['auth','permission:cashier']);
    Route::post('/superadmin/cashier/checkout',   [\App\Http\Controllers\Api\CashierController::class, 'checkout'])->middleware(['auth','permission:cashier']);
    Route::post('/superadmin/cashier/cancel-pending', [\App\Http\Controllers\Api\CashierController::class, 'cancelPending'])->middleware(['auth','permission:cashier']);
    Route::post('/superadmin/cashier/refund',   [\App\Http\Controllers\Api\CashierController::class, 'refund'])->middleware(['auth','permission:cashier']);
    Route::get('/superadmin/cashier/transactions',[\App\Http\Controllers\Api\CashierController::class, 'transactions'])->middleware(['auth','permission:cashier']);

    // ==========================================
    // PRICE MARKUP PERCENTAGE MANAGEMENT
    // Finance manager requests, main finance approves, owner approves
    // ==========================================
    Route::get('/price-markup/current/{branchId?}', [\App\Http\Controllers\Api\PriceMarkupController::class, 'getCurrentPercentage'])->middleware('auth');
    Route::post('/price-markup/request', [\App\Http\Controllers\Api\PriceMarkupController::class, 'requestPercentageChange'])->middleware('auth');
    Route::get('/price-markup/pending/{branchId?}', [\App\Http\Controllers\Api\PriceMarkupController::class, 'getPendingRequests'])->middleware('auth');
    Route::post('/price-markup/{requestId}/main-finance-approve', [\App\Http\Controllers\Api\PriceMarkupController::class, 'mainFinanceApprove'])->middleware('auth');
    Route::post('/price-markup/{requestId}/owner-approve', [\App\Http\Controllers\Api\PriceMarkupController::class, 'ownerApprove'])->middleware('auth');
    Route::get('/price-markup/history/{branchId}', [\App\Http\Controllers\Api\PriceMarkupController::class, 'getHistory'])->middleware('auth');

    // ==========================================
    // ORDERS - KITCHEN STAFF
    // ==========================================
    Route::patch('/orders/{id}/mark-completed', [\App\Http\Controllers\Api\OrderController::class, 'markCompleted'])->middleware('auth');
    Route::get('/orders/{id}', [\App\Http\Controllers\Api\OrderController::class, 'show'])->middleware('auth');

    Route::get('/owner-dashboard', [OwnerDashboardController::class, 'index']);

    // HR Messaging routes
    Route::prefix('hr')->middleware('auth')->group(function () {
        Route::get('/messages/users', [\App\Http\Controllers\HRMessageController::class, 'users']);
        Route::get('/messages/conversation/{userId}', [\App\Http\Controllers\HRMessageController::class, 'conversation']);
        Route::post('/messages/send', [\App\Http\Controllers\HRMessageController::class, 'send']);

        // HR Positions (Open positions request)
        Route::get('/positions', [\App\Http\Controllers\Api\HrPositionRequestController::class, 'positions']);
        Route::post('/positions/requests', [\App\Http\Controllers\Api\HrPositionRequestController::class, 'store']);

    // HR Positions - Main HR approval endpoints
    Route::get('/positions/requests/pending', [\App\Http\Controllers\Api\HrPositionRequestController::class, 'pendingRequests']);
    
    // Public endpoint (customer landing) - approved open positions
    // Note: keep outside auth middleware group in a separate block if you ever refactor.

        Route::post('/positions/requests/{id}/approve', [\App\Http\Controllers\Api\HrPositionRequestController::class, 'approve']);
        Route::post('/positions/requests/{id}/reject', [\App\Http\Controllers\Api\HrPositionRequestController::class, 'reject']);
    });


    // ==========================================
    // STAFF MANAGEMENT API
    // ==========================================
    Route::prefix('admin')->middleware('auth')->group(function () {
        Route::get('/dashboard',        [DashboardController::class, 'index']);
        Route::get('/staff/branch/default-password', [StaffController::class, 'getBranchDefaultPassword']);
        Route::get('/staff',            [StaffController::class, 'apiIndex']);
        Route::get('/staff/{id}',       [StaffController::class, 'apiShow']);
        Route::post('/staff',           [StaffController::class, 'apiStore']);
        Route::put('/staff/{id}',       [StaffController::class, 'apiUpdate']);
        Route::delete('/staff/{id}',    [StaffController::class, 'apiDestroy']);
        Route::post('/staff/{id}/reset-password', [StaffController::class, 'resetPassword']);
        Route::get('/branches',         [StaffController::class, 'apiBranches']);
        Route::get('/attendance', [\App\Http\Controllers\Admin\AttendanceController::class, 'index']);

        // Admin Finance Routes
        Route::prefix('finance')->group(function () {
            Route::get('/dashboard',    [AdminFinanceController::class, 'dashboard']);
            Route::get('/transactions', [AdminFinanceController::class, 'transactions']);
            Route::get('/reports',      [AdminFinanceController::class, 'reports']);
        });

        // Config endpoint for default password (requires authentication)
        Route::get('/config/default-password', [ConfigController::class, 'defaultPassword']);
    });

    // ==========================================
    // BRANCH MANAGER API - Protected with auth middleware
    // All manager routes require authentication
    // Using 'auth:sanctum,web' middleware to support both Bearer token (Sanctum) AND session (web) auth
    // ==========================================
Route::middleware('auth:sanctum,web')->group(function () {
    Route::apiResource('procurement-requests', \App\Http\Controllers\Api\ProcurementRequestController::class)->except(['show']);
    Route::post('procurement-requests/manual', [\App\Http\Controllers\Api\ProcurementRequestController::class, 'storeManual']);
    Route::get('procurement-requests/requested-products', [\App\Http\Controllers\Api\ProcurementRequestController::class, 'requestedProducts']);
    Route::get('procurement-requests/receipt-submissions', [\App\Http\Controllers\Api\ProcurementRequestController::class, 'receiptSubmissions']);
    Route::get('procurement-requests/{id}/confirmed-suppliers', [\App\Http\Controllers\Api\ProcurementRequestController::class, 'confirmedSuppliers']);
    Route::post('procurement-requests/{id}/status', [\App\Http\Controllers\Api\ProcurementRequestController::class, 'updateStatus']);
    Route::post('procurement-requests/{id}/complete', [\App\Http\Controllers\Api\ProcurementRequestController::class, 'completeOrder']);
    Route::post('procurement-requests/{id}/confirm-receipt', [\App\Http\Controllers\Api\ProcurementRequestController::class, 'confirmReceipt']);
    Route::post('procurement-requests/{id}/broadcast', [\App\Http\Controllers\Api\ProcurementRequestController::class, 'broadcastToSuppliers']);

    // Product Request Workflow (Logistics requests new products for approval by Owner)
    Route::apiResource('product-requests', \App\Http\Controllers\Api\ProductRequestController::class)->except(['show', 'update', 'destroy']);
    Route::get('product-requests/pending', [\App\Http\Controllers\Api\ProductRequestController::class, 'getPendingRequests']);
    Route::post('product-requests/{id}/approve', [\App\Http\Controllers\Api\ProductRequestController::class, 'approveRequest']);
    Route::post('product-requests/{id}/reject', [\App\Http\Controllers\Api\ProductRequestController::class, 'rejectRequest']);

    // Multi-level Product Request Approval Workflow
    Route::get('product-requests/pending/logistics', [\App\Http\Controllers\Api\ProductRequestController::class, 'getPendingLogisticsApproval']);
    Route::get('product-requests/pending/owner', [\App\Http\Controllers\Api\ProductRequestController::class, 'getPendingOwnerApproval']);
    Route::post('product-requests/{id}/approve-logistics', [\App\Http\Controllers\Api\ProductRequestController::class, 'approveAtLogistics']);
    Route::post('product-requests/{id}/reject-logistics', [\App\Http\Controllers\Api\ProductRequestController::class, 'rejectAtLogistics']);
    Route::post('product-requests/{id}/approve-owner', [\App\Http\Controllers\Api\ProductRequestController::class, 'approveAtOwner']);
    Route::post('product-requests/{id}/reject-owner', [\App\Http\Controllers\Api\ProductRequestController::class, 'rejectAtOwner']);

    // Backwards-compatible owner-prefixed endpoints used by frontend
    Route::get('owner/product-requests/pending', [\App\Http\Controllers\Api\ProductRequestController::class, 'getPendingOwnerApproval']);
    Route::get('owner/product-requests/approved', [\App\Http\Controllers\Api\ProductRequestController::class, 'getOwnerApprovedRequests']);
    Route::post('owner/product-requests/{id}/approve', [\App\Http\Controllers\Api\ProductRequestController::class, 'approveAtOwner']);
    Route::post('owner/product-requests/{id}/reject', [\App\Http\Controllers\Api\ProductRequestController::class, 'rejectAtOwner']);

    // Product Approval Workflow (Multi-level product approval: Branch -> Logistics -> Owner)
    Route::get('products/approvals/pending-logistics', [\App\Http\Controllers\Api\ProductApprovalController::class, 'getPendingLogisticsApproval']);
    Route::get('products/approvals/pending-owner', [\App\Http\Controllers\Api\ProductApprovalController::class, 'getPendingOwnerApproval']);
    Route::post('products/{productId}/approvals/logistics/approve', [\App\Http\Controllers\Api\ProductApprovalController::class, 'approveAtLogistics']);
    Route::post('products/{productId}/approvals/logistics/reject', [\App\Http\Controllers\Api\ProductApprovalController::class, 'rejectAtLogistics']);
    Route::post('products/{productId}/approvals/owner/approve', [\App\Http\Controllers\Api\ProductApprovalController::class, 'approveAtOwner']);
    Route::post('products/{productId}/approvals/owner/reject', [\App\Http\Controllers\Api\ProductApprovalController::class, 'rejectAtOwner']);

    Route::apiResource('procurement.products', \App\Http\Controllers\Api\ProcurementProductController::class)->only(['index']);
    Route::post('procurement.products/{productId}/place-order', [\App\Http\Controllers\Api\ProcurementProductController::class, 'placeOrder']);

    Route::apiResource('supplier-orders', \App\Http\Controllers\Api\SupplierOrderController::class)->only(['index']);
    Route::put('supplier-orders/{id}/status', [\App\Http\Controllers\Api\SupplierOrderController::class, 'updateStatus']);
    Route::post('supplier-orders/{id}/submit-product', [\App\Http\Controllers\Api\SupplierOrderController::class, 'submitProduct']);
});

Route::prefix('manager')->middleware('auth:sanctum,web')->group(function () {
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
        Route::get('/hr/attendance', [\App\Http\Controllers\Api\ManagerProfileController::class, 'hrAttendance']);
        Route::get('/hr/staff', [\App\Http\Controllers\Api\ManagerProfileController::class, 'hrStaff']);
        Route::post('/hr/staff', [\App\Http\Controllers\Api\ManagerProfileController::class, 'createHrStaff']);
        Route::put('/hr/staff/{id}', [\App\Http\Controllers\Api\ManagerProfileController::class, 'updateHrStaff']);
        Route::delete('/hr/staff/{id}', [\App\Http\Controllers\Api\ManagerProfileController::class, 'deleteHrStaff']);
        Route::get('/hr/reports', [\App\Http\Controllers\Api\ManagerProfileController::class, 'hrReports']);

        Route::get('/finance/profile', [\App\Http\Controllers\Api\ManagerProfileController::class, 'financeProfile']);
        Route::put('/finance/profile', [\App\Http\Controllers\Api\ManagerProfileController::class, 'updateFinanceProfile']);

        Route::get('/logistics/profile', [\App\Http\Controllers\Api\ManagerProfileController::class, 'logisticsProfile']);
        Route::put('/logistics/profile', [\App\Http\Controllers\Api\ManagerProfileController::class, 'updateLogisticsProfile']);
        Route::get('/logistics/dashboard', [\App\Http\Controllers\Api\ManagerProfileController::class, 'logisticsDashboard']);
        Route::get('/logistics/deliveries', [\App\Http\Controllers\Api\ManagerProfileController::class, 'logisticsDeliveries']);
        Route::get('/logistics/products', [\App\Http\Controllers\Api\ManagerProfileController::class, 'logisticsProducts']);
        Route::get('/logistics/branches', [\App\Http\Controllers\Api\ManagerProfileController::class, 'logisticsBranches']);
        Route::get('/logistics/suppliers', [\App\Http\Controllers\Api\ManagerProfileController::class, 'logisticsSuppliers']);

        // Allow logistics managers to confirm procurement stock via manager logistics endpoints
        Route::post('/logistics/procurements/{id}/confirm-stock', [\App\Http\Controllers\Staff\StaffInventoryController::class, 'confirmProcurementStock']);

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

        // Finance Manager Dashboard & Reports (ManagerFinanceController) - PRIMARY endpoints for panel
        Route::get('/finance/profile', [\App\Http\Controllers\Api\ManagerFinanceController::class, 'profile']);
        Route::get('/finance/dashboard', [\App\Http\Controllers\Api\ManagerFinanceController::class, 'dashboard']);
        Route::get('/finance/reports', [\App\Http\Controllers\Api\ManagerFinanceController::class, 'reports']);
        Route::get('/finance/transactions', [\App\Http\Controllers\Api\ManagerFinanceController::class, 'transactions']);

        // Branch budget management (Finance Manager) - list and update branch budgets
        Route::get('/finance/branches', [\App\Http\Controllers\Api\ManagerFinanceController::class, 'branches']);
        Route::put('/finance/branches/{id}/budget', [\App\Http\Controllers\Api\ManagerFinanceController::class, 'updateBranchBudget']);

        Route::get('/inventory/profile', [\App\Http\Controllers\Api\ManagerProfileController::class, 'invProfile']);
        Route::put('/inventory/profile', [\App\Http\Controllers\Api\ManagerProfileController::class, 'updateInvProfile']);
        Route::get('/inventory/dashboard', [\App\Http\Controllers\Api\ManagerProfileController::class, 'invDashboard']);
        Route::get('/inventory/products', [\App\Http\Controllers\Api\ManagerProfileController::class, 'invProducts']);
        Route::get('/inventory/reports', [\App\Http\Controllers\Api\ManagerProfileController::class, 'invReports']);
        Route::get('/inventory/pending-procurements', [\App\Http\Controllers\Api\ManagerProfileController::class, 'invPendingProcurements']);
        Route::get('/inventory/confirmed-procurements', [\App\Http\Controllers\Api\ManagerProfileController::class, 'invConfirmedProcurements']);
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

            // Pending procurements awaiting stock confirmation (staff)
            Route::get('/inventory/pending-procurements', [\App\Http\Controllers\Staff\StaffInventoryController::class, 'pendingProcurements']);
            Route::get('/inventory/confirmed-procurements', [\App\Http\Controllers\Staff\StaffInventoryController::class, 'confirmedProcurements']);
            Route::get('/inventory/variance-alerts', [\App\Http\Controllers\Staff\StaffInventoryController::class, 'varianceAlerts']);
            Route::post('/inventory/procurements/{id}/confirm-stock', [\App\Http\Controllers\Staff\StaffInventoryController::class, 'confirmProcurementStock']);

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

            // Kitchen - Dish creation and ingredient submission
            Route::get('/kitchen/dishes', [\App\Http\Controllers\Staff\KitchenDishController::class, 'index'])->middleware(['auth', 'permission:kitchen,fn:kitchen.orders']);
            Route::post('/kitchen/dishes', [\App\Http\Controllers\Staff\KitchenDishController::class, 'store'])->middleware(['auth', 'permission:kitchen,fn:kitchen.production']);
            Route::post('/kitchen/dishes/{id}/produce', [\App\Http\Controllers\Staff\KitchenDishController::class, 'produce'])->middleware(['auth', 'permission:kitchen,fn:kitchen.production']);
            Route::post('/kitchen/ingredients/{id}/low-stock', [\App\Http\Controllers\Staff\KitchenDishController::class, 'markLowStock'])->middleware(['auth', 'permission:kitchen,fn:kitchen.waste']);
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

            // Dish Approval Workflow - Owner must approve new dishes before they appear in logistics
            Route::get('/dishes/pending', [\App\Http\Controllers\Admin\DishApprovalController::class, 'pendingDishes']);
            Route::get('/dishes/approved', [\App\Http\Controllers\Admin\DishApprovalController::class, 'approvedDishes']);
            Route::post('/dishes/{id}/approve', [\App\Http\Controllers\Admin\DishApprovalController::class, 'approveDish']);
            Route::post('/dishes/{id}/reject', [\App\Http\Controllers\Admin\DishApprovalController::class, 'rejectDish']);
            Route::post('/dishes/{id}/publish', [\App\Http\Controllers\Admin\DishApprovalController::class, 'publishDish']);
        });
    });

    // PRODUCT COMMENTS API
    Route::get('/products-for-comments', [ProductCommentController::class, 'listProducts']);
    // Public branches list for customer landing (no auth required)
    Route::get('/public/branches', [ProductCommentController::class, 'publicBranches']);

    // Public approved positions for customer landing (no auth required)
    Route::get('/public/positions/approved', [\App\Http\Controllers\Api\HrPositionRequestController::class, 'approvedOpenPositions']);

    Route::get('/product-comments', [ProductCommentController::class, 'index']);
    Route::get('/product-comments/all', [ProductCommentController::class, 'allComments'])->middleware('auth');
    Route::post('/product-comments', [ProductCommentController::class, 'store']);
    Route::post('/product-comment-replies', [ProductCommentController::class, 'storeReply']);
    Route::delete('/product-comments/{id}', [ProductCommentController::class, 'destroy'])->middleware('auth');
    Route::post('/product-comments/{id}/flag', [ProductCommentController::class, 'flag'])->middleware('auth');
    Route::post('/product-comments/{id}/unhide', [ProductCommentController::class, 'unhide'])->middleware('auth');

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

    // ==========================================
    // LOCATIONS API - REGIONS, PROVINCES, CITIES, BARANGAYS
    // ==========================================
    Route::prefix('locations')->middleware('auth')->group(function () {
        Route::get('/regions', [LocationController::class, 'regions']);
        Route::get('/provinces', [LocationController::class, 'provinces']);
        Route::get('/cities', [LocationController::class, 'cities']);
        Route::get('/barangays', [LocationController::class, 'barangays']);
    });
});
