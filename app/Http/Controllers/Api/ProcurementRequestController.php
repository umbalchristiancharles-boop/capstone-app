<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProcurementRequest;
use App\Models\SupplierOrder;
use App\Models\Product;
use App\Models\User;
use App\Models\Branch;
use App\Models\BudgetRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ProcurementRequestController extends Controller
{
    /**
     * Check if user has procurement access (including CUSTOM role with procurement module)
     */
    private function canAccessProcurement($user): bool
    {
        if (!$user) return false;

        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');

        // Check standard roles
        if ($role === 'SUPER_ADMIN') return true;
        if ($role === 'PROCUREMENT_MANAGER') return true;
        if ($role === 'MANAGER' && $dept === 'PROCUREMENT') return true;

        // Check CUSTOM role with procurement module
        if ($role === 'CUSTOM') {
            try {
                $perms = $user->permissions ?? [];
                if (is_string($perms)) $perms = json_decode($perms, true) ?: [];
                $modules = [];
                if (is_array($perms) && isset($perms['modules']) && is_array($perms['modules'])) {
                    $modules = $perms['modules'];
                } elseif (is_array($perms)) {
                    $modules = $perms;
                }
                foreach ($modules as $m) {
                    if (strtoupper(trim((string)$m)) === 'PROCUREMENT') return true;
                }
            } catch (\Throwable $e) { /* ignore */ }
        }

        return false;
    }

    /**
     * Check if user has CUSTOM role with logistics module
     */
    private function hasCustomLogisticsAccess($user): bool
    {
        if (!$user) return false;

        $role = strtoupper($user->role ?? '');
        if ($role !== 'CUSTOM') return false;

        try {
            $perms = $user->permissions ?? [];
            if (is_string($perms)) $perms = json_decode($perms, true) ?: [];
            $modules = [];
            if (is_array($perms) && isset($perms['modules']) && is_array($perms['modules'])) {
                $modules = $perms['modules'];
            } elseif (is_array($perms)) {
                $modules = $perms;
            }
            foreach ($modules as $m) {
                if (strtoupper(trim((string)$m)) === 'LOGISTICS') return true;
            }
        } catch (\Throwable $e) { /* ignore */ }

        return false;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');

        $query = ProcurementRequest::with(['product', 'logisticsUser', 'procurementUser', 'financeUser'])
            ->orderBy('created_at', 'desc');

        // If frontend requests to include completed/archived requests, allow it
        $includeCompleted = $request->boolean('include_completed', false);

        // Allow Super Admin to view requests across branches (optionally filtered by branch_id)
        if ($role === 'SUPER_ADMIN') {
            $branchFilter = $request->query('branch_id');
            if ($branchFilter) {
                $query->where('branch_id', $branchFilter);
            }
        } elseif (in_array($role, ['LOGISTICS_MANAGER', 'MANAGER_LOGISTICS']) || ($role === 'MANAGER' && $dept === 'LOGISTICS') || $this->hasCustomLogisticsAccess($user)) {
            // Logistics managers normally see requests they created (logistics_user_id)
            // However, a MAIN BRANCH logistics manager may select a branch to view
            $isMainBranch = false;
            try {
                if ($user->branch_id) {
                    $branch = Branch::find($user->branch_id);
                    if ($branch) {
                        $branchName = strtoupper(trim((string) ($branch->name ?? '')));
                        $isMainBranch = ((int) $branch->id === 1) || str_contains($branchName, 'MAIN BRANCH');
                    }
                }
            } catch (\Exception $e) {
                // ignore and treat as non-main branch
                $isMainBranch = false;
            }

            $branchFilter = $request->query('branch_id');
            if ($isMainBranch && $branchFilter) {
                // Allow main-branch logistics to view requests for the selected branch
                $query->where('branch_id', (int) $branchFilter);
            } else {
                // Default: only show requests created by this logistics user
                $query->where('logistics_user_id', $user->id);
            }
        } elseif ($role === 'PROCUREMENT_MANAGER') {
            // Procurement sees procurement lifecycle states for branch. By default exclude 'completed'
            $query->where('branch_id', $user->branch_id ?? 1);
            if (!$includeCompleted) {
                $query->whereIn('status', ['pending', 'approved', 'budget_pending', 'cash_in_transit', 'pending_order_to_supplier']);
            }
        } elseif (in_array($role, ['FINANCE_MANAGER', 'MANAGER_FINANCE'])) {
            // Finance sees pending budget approvals and items they need to confirm (cash in transit)
            $query->where(function($q) use ($includeCompleted) {
                if ($includeCompleted) {
                    // include everything if requested
                    $q->whereRaw('1 = 1');
                } else {
                    $q->where('budget_approved', false)
                      ->orWhere('status', 'cash_in_transit');
                }
            });
        } else {
            // Default branch filter
            $query->where('branch_id', $user->branch_id ?? 1);
        }

        $requests = $query->paginate(20);

        return response()->json($requests);
    }

    /**
     * Broadcast a procurement request to suppliers so they can submit product/price.
     * Optional payload: supplier_ids => [1,2,3]
     */
    public function broadcastToSuppliers(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        if (!$this->canAccessProcurement($user)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $procRequest = ProcurementRequest::with('product')->findOrFail($id);

        // If product already has supplier and price, broadcasting is unnecessary
        if ($procRequest->product && !empty($procRequest->product->supplier_id) && (float)($procRequest->product->price ?? 0) > 0) {
            return response()->json(['error' => 'Product already has supplier and price'], 400);
        }

        $validated = $request->validate([
            'supplier_ids' => 'nullable|array',
            'supplier_ids.*' => 'integer|exists:users,id'
        ]);

        // Determine target suppliers
        if (!empty($validated['supplier_ids'])) {
            $suppliers = User::whereIn('id', $validated['supplier_ids'])->where('role', 'SUPPLIER')->get();
        } else {
            // Default: all active suppliers (optionally filter by branch if desired)
            $suppliers = User::where('role', 'SUPPLIER')->where(function($q) use ($procRequest) {
                $q->whereNull('branch_id')->orWhere('branch_id', $procRequest->branch_id);
            })->get();
        }

        if ($suppliers->isEmpty()) {
            return response()->json(['error' => 'No suppliers found to broadcast to'], 400);
        }

        $created = [];
        foreach ($suppliers as $s) {
            // Skip if an order for this procurement_request and supplier already exists
            $exists = SupplierOrder::where('procurement_request_id', $procRequest->id)
                ->where('supplier_id', $s->id)
                ->first();
            if ($exists) continue;

            $order = SupplierOrder::create([
                'procurement_request_id' => $procRequest->id,
                'product_id' => $procRequest->product_id ?? null,
                'supplier_id' => $s->id,
                'quantity' => $procRequest->quantity ?? 1,
                'status' => 'pending',
                'is_broadcast' => true,
                'branch_id' => $procRequest->branch_id,
            ]);

            $created[] = $order;
        }

        return response()->json(['ok' => true, 'created' => count($created), 'orders' => $created]);
    }

    public function store(Request $request)
    {
        Log::info('=== PROCUREMENT REQUEST STORE START ===');
        Log::info('User', ['id' => $request->user()?->id, 'role' => $request->user()?->role, 'department' => $request->user()?->department, 'branch_id' => $request->user()?->branch_id]);
        Log::info('Input payload', $request->all());

        $user = $request->user();
        if (!$user) {
            Log::error('NO AUTHENTICATED USER');
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');
        Log::info('Role check', ['role' => $role, 'dept' => $dept]);

        // Check if user has logistics access (standard roles or CUSTOM with logistics)
        $hasLogisticsAccess = (
            $role === 'LOGISTICS_MANAGER' || $role === 'MANAGER_LOGISTICS' ||
            ($role === 'MANAGER' && $dept === 'LOGISTICS') ||
            $role === 'SUPER_ADMIN'
        );

        // Check CUSTOM role with logistics module
        if (!$hasLogisticsAccess && $role === 'CUSTOM') {
            try {
                $perms = $user->permissions ?? [];
                if (is_string($perms)) $perms = json_decode($perms, true) ?: [];
                $modules = [];
                if (is_array($perms) && isset($perms['modules']) && is_array($perms['modules'])) {
                    $modules = $perms['modules'];
                } elseif (is_array($perms)) {
                    $modules = $perms;
                }
                foreach ($modules as $m) {
                    if (strtoupper(trim((string)$m)) === 'LOGISTICS') {
                        $hasLogisticsAccess = true;
                        break;
                    }
                }
            } catch (\Throwable $e) { /* ignore */ }
        }

        if (!$hasLogisticsAccess) {
            Log::error('UNAUTHORIZED ROLE', ['role' => $role, 'dept' => $dept]);
            return response()->json(['error' => 'Unauthorized role'], 401);
        }

        if ($role !== 'SUPER_ADMIN' && $user->branch_id) {
            $branch = Branch::find($user->branch_id);
            $branchName = strtoupper(trim((string) ($branch->name ?? '')));
            $isMainBranch = $branch && ((int) $branch->id === 1 || str_contains($branchName, 'MAIN BRANCH'));

            if ($isMainBranch) {
                Log::warning('PROC REQUEST BLOCKED FOR MAIN BRANCH LOGISTICS', ['user_id' => $user->id, 'branch_id' => $user->branch_id]);
                return response()->json(['error' => 'Main Branch logistics cannot create procurement requests.'], 403);
            }
        }

        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);
            Log::info('Validation OK', $validated);
        } catch (\Exception $e) {
            Log::error('VALIDATION FAILED', ['error' => $e->getMessage()]);
            throw $e;
        }

        try {
            $product = Product::findOrFail($validated['product_id']);
            Log::info('Product found', ['id' => $product->id, 'name' => $product->name, 'branch_id' => $product->branch_id, 'price' => $product->price]);
        } catch (\Exception $e) {
            Log::error('PRODUCT NOT FOUND', ['product_id' => $validated['product_id'], 'error' => $e->getMessage()]);
            throw $e;
        }

        if ($role !== 'SUPER_ADMIN' && $user->branch_id && $product->branch_id != $user->branch_id) {
            Log::error('BRANCH MISMATCH', ['user_branch' => $user->branch_id, 'product_branch' => $product->branch_id]);
            return response()->json(['error' => 'Product not in your branch'], 403);
        }

        if ($role === 'SUPER_ADMIN') {
            $branchId = $request->input('branch_id') ?? $product->branch_id ?? 1;
        } else {
            $branchId = $user->branch_id ?: 1;
        }
        Log::info('Using branch_id', ['branch_id' => $branchId]);

        $data = [
            'product_id' => $validated['product_id'],
            'logistics_user_id' => $user->id,
            'quantity' => $validated['quantity'],
            'price' => $product->price,
            'total_amount' => $product->price * $validated['quantity'],
            'status' => 'pending',
            'budget_approved' => false,
            'branch_id' => $branchId,
        ];
        Log::info('Creating ProcurementRequest', $data);

        try {
            $procRequest = ProcurementRequest::create($data);
            Log::info('ProcRequest CREATED', ['id' => $procRequest->id]);
        } catch (\Exception $e) {
            Log::error('CREATE FAILED', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }

        try {
            $product->update(['logistics_request_available' => true, 'has_been_ordered' => true]);
            Log::info('Product updated');
        } catch (\Exception $e) {
            Log::error('PRODUCT UPDATE FAILED', ['error' => $e->getMessage()]);
        }

        $result = $procRequest->load(['product', 'logisticsUser']);
        Log::info('=== STORE SUCCESS ===', ['request_id' => $procRequest->id]);
        return response()->json($result, 201);
    }

public function requestedProducts(Request $request)
    {
        Log::info('=== REQUESTED PRODUCTS START ===', ['user_id' => $request->user()?->id, 'user_role' => $request->user()?->role, 'user_branch' => $request->user()?->branch_id]);

        $user = $request->user();
        if (!$user) {
            Log::error('NO USER - 401');
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');

        $isProcurementManager = $role === 'PROCUREMENT_MANAGER';
        $isManagerProcurement = ($role === 'MANAGER' && $dept === 'PROCUREMENT');
        $isSuperAdmin = $role === 'SUPER_ADMIN';
        $isAllowed = $isProcurementManager || $isManagerProcurement || $isSuperAdmin;

        // Allow CUSTOM accounts with procurement module permission
        if (!$isAllowed && $role === 'CUSTOM') {
            try {
                $perms = $user->permissions ?? [];
                if (is_string($perms)) $perms = json_decode($perms, true) ?: [];
                $modules = [];
                if (is_array($perms) && isset($perms['modules']) && is_array($perms['modules'])) {
                    $modules = $perms['modules'];
                } elseif (is_array($perms)) {
                    $modules = $perms;
                }
                foreach ($modules as $m) {
                    if (strtoupper(trim((string)$m)) === 'PROCUREMENT') { $isAllowed = true; break; }
                }
            } catch (\Throwable $e) { /* ignore */ }
        }

        if (!$isAllowed) {
            Log::warning('UNAUTHORIZED ROLE', ['role' => $role, 'dept' => $dept]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $branchId = $isSuperAdmin ? ($request->query('branch_id') ?? 1) : ($user->branch_id ?? 1);
        Log::info('Querying pending requests', ['branch_id' => $branchId]);

        try {
            $requests = ProcurementRequest::with(['product:id,name,price,sku,branch_id,supplier_id,logistics_request_available'])
                ->where('branch_id', $branchId)
                    ->whereIn('status', ['pending', 'budget_pending', 'pending_order_to_supplier', 'delivery_pending', 'ongoing_delivery'])
                ->get(['id', 'product_id', 'branch_id', 'status', 'budget_approved', 'supplier_confirmed', 'receipt_confirmed', 'receipt_path']);
            Log::info('Requests fetched', ['count' => $requests->count()]);

            if ($requests->isEmpty()) {
                Log::info('No pending requests found');
                return response()->json([]);
            }

            $productIds = $requests->pluck('product_id')->filter();
            Log::info('Product IDs', ['ids' => $productIds->toArray()]);

            $products = Product::whereIn('id', $productIds)
                ->where('branch_id', $branchId)
                ->with(['supplier:id,username,full_name'])
                ->get(['id', 'name', 'price', 'sku', 'branch_id', 'supplier_id', 'logistics_request_available']);

            Log::info('Products fetched', ['count' => $products->count()]);
            Log::info('=== REQUESTED PRODUCTS SUCCESS ===');

            // Attach the procurement request id to each product so the frontend
            // can reference the correct procurement_request when acting on it.
            $requestsByProduct = $requests->keyBy('product_id');

            // Precompute which procurement requests were broadcast to suppliers
            $broadcastedProcReqIds = \App\Models\SupplierOrder::whereIn('procurement_request_id', $requests->pluck('id')->toArray())
                ->where('is_broadcast', true)
                ->pluck('procurement_request_id')
                ->unique()
                ->toArray();

            // Precompute which procurement requests have confirmed suppliers (have product_id in SupplierOrder)
            $confirmedSupplierProcReqIds = \App\Models\SupplierOrder::whereIn('procurement_request_id', $requests->pluck('id')->toArray())
                ->whereNotNull('product_id')
                ->pluck('procurement_request_id')
                ->unique()
                ->toArray();

            $products = $products->map(function ($p) use ($requestsByProduct, $broadcastedProcReqIds, $confirmedSupplierProcReqIds) {
                $req = $requestsByProduct->get($p->id);
                $p->procurement_request_id = $req ? $req->id : null;
                $p->procurement_status = $req ? $req->status : null;
                $p->procurement_budget_approved = $req ? (bool)$req->budget_approved : false;

                // Include receipt info so procurement UI can react when finance confirms
                $p->receipt_confirmed = $req ? (bool)$req->receipt_confirmed : false;
                $p->receipt_path = $req ? ($req->receipt_path ?? null) : null;

                // If finance confirmed, expose a virtual status for the UI
                if ($p->receipt_confirmed) {
                    $p->procurement_status = 'receipt_confirmed';
                }

                // supplier_confirmed reflects the procurement request flag only.
                $p->supplier_confirmed = $req ? (bool)$req->supplier_confirmed : false;

                // Determine if this request was broadcast to suppliers and still
                // waiting for supplier confirmation. Only in that case should the
                // UI block placing orders and show the waiting message.
                $wasBroadcast = $req ? in_array($req->id, $broadcastedProcReqIds) : false;
                $p->waiting_for_supplier = $wasBroadcast && !$p->supplier_confirmed;

                // Determine if procurement can acknowledge this request. If the product
                // has no supplier or a non-positive price, procurement should NOT
                // acknowledge and must request supplier input first.
                // BUT if suppliers have already confirmed (have products), no need to request them
                $p->needs_supplier = true;
                if (!empty($p->supplier_id) && (float)($p->price ?? 0) > 0) {
                    $p->needs_supplier = false;
                } elseif ($req && in_array($req->id, $confirmedSupplierProcReqIds)) {
                    // Suppliers have confirmed - don't need to request more suppliers
                    $p->needs_supplier = false;
                }

                return $p;
            });

            return response()->json($products);
        } catch (\Exception $e) {
            Log::error('REQUESTED PRODUCTS ERROR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'branch_id' => $branchId,
                'user_id' => $user->id
            ]);
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Return procurement requests that have receipts uploaded and awaiting finance confirmation.
     */
    public function receiptSubmissions(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');
        $allowed = false;
        if (in_array($role, ['FINANCE_MANAGER', 'MANAGER_FINANCE']) || ($role === 'MANAGER' && $dept === 'FINANCE')) {
            $allowed = true;
        }
        // Allow CUSTOM accounts with finance module permission
        if (!$allowed && $role === 'CUSTOM') {
            try {
                $perms = $user->permissions ?? [];
                if (is_string($perms)) $perms = json_decode($perms, true) ?: [];
                $modules = [];
                if (is_array($perms) && isset($perms['modules']) && is_array($perms['modules'])) {
                    $modules = $perms['modules'];
                } elseif (is_array($perms)) {
                    $modules = $perms;
                }
                foreach ($modules as $m) {
                    if (strtoupper(trim((string)$m)) === 'FINANCE') { $allowed = true; break; }
                }
            } catch (\Throwable $e) { /* ignore */ }
        }
        if (!$allowed) return response()->json(['error' => 'Unauthorized'], 401);

        try {
            $query = ProcurementRequest::with(['product', 'logisticsUser', 'branch'])
                ->whereNotNull('receipt_path')
                ->where('receipt_confirmed', false)
                ->orderBy('receipt_uploaded_at', 'desc');

            // Finance managers may want to filter by branch
            if ($role !== 'SUPER_ADMIN' && $user->branch_id) {
                $query->where('branch_id', $user->branch_id);
            }

            $requests = $query->get();
            return response()->json(['ok' => true, 'requests' => $requests]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch receipt submissions', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch submissions'], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        // Try request user first, then fall back to common API guards (sanctum/api)
        $user = $request->user();
        if (!$user) {
            $user = auth('sanctum')->user() ?: auth('api')->user();
        }

        Log::info('updateStatus called', [
            'user_id' => $user?->id ?? null,
            'role' => $user?->role ?? null,
            'auth_header' => $request->header('authorization')
        ]);

        if (!$user) {
            Log::warning('updateStatus unauthenticated request', ['id' => $id]);
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');

        $procRequest = ProcurementRequest::with('product')->findOrFail($id);

        // Allow either explicit PROCUREMENT_MANAGER role or a branch Manager in PROCUREMENT or CUSTOM with procurement
        $canAccessProcurement = $role === 'PROCUREMENT_MANAGER' || ($role === 'MANAGER' && $dept === 'PROCUREMENT') || $this->canAccessProcurement($user);
        if ($canAccessProcurement && $procRequest->status === 'pending') {
            // Handle multi-supplier scenarios:
            // 1. Get confirmed suppliers (those who submitted products)
            $confirmedSuppliers = SupplierOrder::where('procurement_request_id', $procRequest->id)
                ->whereNotNull('product_id')
                ->with(['supplier', 'product'])
                ->get();

            // Accept optional supplier_id from request for multi-supplier selection
            $selectedSupplierId = $request->input('supplier_id');

            Log::info('updateStatus multi-supplier check', [
                'proc_id' => $procRequest->id,
                'confirmed_count' => $confirmedSuppliers->count(),
                'selectedSupplierId_input' => $selectedSupplierId,
                'selectedSupplierId_type' => gettype($selectedSupplierId),
                'request_body' => $request->all(),
                'is_empty' => empty($selectedSupplierId)
            ]);

            if ($confirmedSuppliers->count() > 1 && !$selectedSupplierId) {
                // Multiple suppliers exist but none selected
                Log::info('Multi-supplier acknowledge: need supplier selection', ['proc_id' => $procRequest->id, 'confirmed_count' => $confirmedSuppliers->count()]);
                return response()->json([
                    'error' => 'Multiple suppliers have confirmed this product. Please select one.',
                    'confirmed_suppliers' => $confirmedSuppliers->count(),
                    'need_supplier_selection' => true
                ], 400);
            }

            // Determine the supplier order to use for pricing
            $selectedOrder = null;
            $selectedProduct = null;

            if ($selectedSupplierId) {
                // User selected a specific supplier from confirmed list
                $selectedOrder = $confirmedSuppliers->firstWhere('supplier_id', $selectedSupplierId);
                if (!$selectedOrder) {
                    return response()->json(['error' => 'Selected supplier not found in confirmed suppliers'], 400);
                }
                $selectedProduct = $selectedOrder->product;
                Log::info('Acknowledge with multi-supplier selection', ['proc_id' => $procRequest->id, 'selected_supplier' => $selectedSupplierId, 'selected_product_id' => $selectedProduct->id]);
            } elseif ($confirmedSuppliers->count() === 1) {
                // Single confirmed supplier, use it automatically
                $selectedOrder = $confirmedSuppliers->first();
                $selectedProduct = $selectedOrder->product;
                $selectedSupplierId = $selectedOrder->supplier_id;
                Log::info('Acknowledge with single confirmed supplier', ['proc_id' => $procRequest->id, 'supplier_id' => $selectedSupplierId]);
            } else {
                // No confirmed suppliers yet, check original product
                $product = $procRequest->product;
                if (!$product || empty($product->supplier_id) || (float)($product->price ?? 0) <= 0) {
                    Log::info('Procurement acknowledge blocked: missing supplier or price', ['proc_id' => $procRequest->id, 'product_id' => $product?->id ?? null, 'supplier_id' => $product?->supplier_id ?? null, 'price' => $product?->price ?? null]);
                    return response()->json(['error' => 'Cannot acknowledge procurement: product has no supplier or price. Request suppliers to submit product and set price first.'], 400);
                }
                $selectedProduct = $product;
            }

            if (!$selectedProduct) {
                return response()->json(['error' => 'Could not determine product for budget request'], 400);
            }

            try {
                // Procurement acknowledges and auto-creates BudgetRequest for Finance panel
                $budgetCreated = false;
                DB::transaction(function () use ($procRequest, $user, $selectedProduct, $selectedSupplierId, &$budgetCreated) {
                    $updateData = [
                        'procurement_user_id' => $user->id,
                        'status' => 'budget_pending',
                        'supplier_confirmed' => true  // Mark that supplier is confirmed
                    ];
                    
                    // Store the selected supplier if available
                    if ($selectedSupplierId) {
                        $updateData['supplier_id'] = $selectedSupplierId;
                    }
                    
                    $procRequest->update($updateData);

                    // Use price from selected product (either supplier's or original)
                    $budgetAmount = $selectedProduct->price * max(1, $procRequest->quantity);

                    // Check if BudgetRequest already exists for this procurement request
                    $existingBudget = BudgetRequest::where('branch_id', $procRequest->branch_id)
                        ->where('purpose', 'LIKE', "%Procurement Request #{$procRequest->id}%")
                        ->first();

                    if (!$existingBudget) {
                        BudgetRequest::create([
                            'branch_id' => $procRequest->branch_id,
                            'user_id' => $user->id, // procurement manager as requester
                            'purpose' => "Procurement Request #{$procRequest->id}: {$selectedProduct->name} x{$procRequest->quantity}",
                            'requested_amount' => $budgetAmount,
                            'status' => 'Pending',
                            'date_requested' => now()->toDateString(),
                        ]);
                        $budgetCreated = true;

                        Log::info('Auto-created BudgetRequest from ProcurementRequest', [
                            'proc_req_id' => $procRequest->id,
                            'budget_user_id' => $user->id,
                            'branch_id' => $procRequest->branch_id,
                            'budget_amount' => $budgetAmount
                        ]);
                    } else {
                        Log::info('BudgetRequest already exists, skipping creation', [
                            'proc_req_id' => $procRequest->id,
                            'budget_id' => $existingBudget->id
                        ]);
                    }
                });
                Log::info('Procurement acknowledgment successful', ['proc_req_id' => $procRequest->id, 'budget_created' => $budgetCreated, 'supplier_id' => $selectedSupplierId]);
            } catch (\Exception $e) {
                Log::error('Procurement acknowledgment failed', [
                    'proc_req_id' => $procRequest->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json(['error' => 'Failed to acknowledge: ' . $e->getMessage()], 500);
            }
        } elseif (in_array($role, ['FINANCE_MANAGER', 'MANAGER_FINANCE']) || ($role === 'MANAGER' && $dept === 'FINANCE')) {
            // Finance first approval: provide budget_amount -> mark budget_approved and cash in transit
            if (!$procRequest->budget_approved) {
                $validated = $request->validate(['budget_amount' => 'required|numeric|min:0']);
                if ($validated['budget_amount'] < $procRequest->total_amount) {
                    return response()->json(['error' => 'Budget must cover total amount'], 400);
                }
                $procRequest->update([
                    'finance_user_id' => $user->id,
                    'budget_amount' => $validated['budget_amount'],
                    'budget_approved' => true,
                    'status' => 'cash_in_transit', // funds are on their way
                ]);
            } elseif ($procRequest->budget_approved && $procRequest->status === 'cash_in_transit') {
                // Finance confirms cash was given physically -> move to pending order to supplier.
                // Procurement still performs the explicit place-order action.
                try {
                    DB::transaction(function () use ($procRequest, $user) {
                        $procRequest->update([
                            'status' => 'pending_order_to_supplier'
                        ]);
                    });
                } catch (\Exception $e) {
                    Log::error('Transaction failed while setting pending_order_to_supplier', ['error' => $e->getMessage(), 'proc_id' => $procRequest->id]);
                    return response()->json(['error' => 'Failed to process procurement order to supplier'], 500);
                }
            } else {
                return response()->json(['error' => 'No action available for this request'], 400);
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Prepare success response with clear message
        $response = $procRequest->fresh()->load(['product', 'logisticsUser', 'procurementUser', 'financeUser']);
        $message = 'Procurement request updated';
        
        // Add context about budget request if status changed to budget_pending
        if ($response->status === 'budget_pending') {
            $message = '✓ Request acknowledged! Budget request has been sent to Finance. Waiting for Finance Manager approval.';
        } elseif ($response->status === 'cash_in_transit') {
            $message = '✓ Budget approved by Finance! Ready for order placement.';
        } elseif ($response->status === 'pending_order_to_supplier') {
            $message = '✓ Cash confirmed! Procurement can now place orders with suppliers.';
        }
        
        return response()->json([
            'ok' => true,
            'message' => $message,
            'procurement_request' => $response
        ]);
    }

    public function completeOrder(Request $request, $id)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');
        // Allow procurement manager or supplier to upload receipt. Actual
        // completion requires finance confirmation and should be handled
        // separately via confirmReceipt.
        $canAccessProcurement = $this->canAccessProcurement($user);
        if (!in_array($role, ['SUPPLIER']) && !($role === 'MANAGER' && $dept === 'PROCUREMENT') && !$canAccessProcurement) {
            Log::warning('UNAUTHORIZED ROLE', ['role' => $role, 'dept' => $dept]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $procRequest = ProcurementRequest::with('product')->findOrFail($id);
        // Acceptable statuses for receipt upload / completion flow
        $allowedForComplete = ['pending_order_to_supplier', 'delivery_pending', 'ongoing_delivery'];

        if ($user->branch_id && $procRequest->branch_id != $user->branch_id) {
            return response()->json(['error' => 'Not your branch'], 403);
        }

        if (!in_array($procRequest->status, $allowedForComplete, true)) {
            return response()->json(['error' => 'Request not in a state that can be completed'], 400);
        }

        // If there's a receipt file in the request, treat this as a supplier
        // uploading the physical receipt. Save the file and mark the request
        // as awaiting finance confirmation.
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            if (!$file->isValid()) {
                return response()->json(['error' => 'Invalid file upload'], 400);
            }

            // If role is SUPPLIER, ensure they are permitted to upload for this request
            if ($role === 'SUPPLIER') {
                if (Schema::hasTable('supplier_orders')) {
                    $supplierOrder = SupplierOrder::where('procurement_request_id', $procRequest->id)
                        ->where('supplier_id', $user->id)
                        ->first();
                    if (!$supplierOrder) {
                        return response()->json(['error' => 'No supplier order found for this supplier'], 403);
                    }
                } else {
                    Log::warning('supplier_orders table missing; skipping supplier verification for receipt upload', ['proc_req_id' => $procRequest->id, 'user_id' => $user->id]);
                }
            }

            // Store file directly under public/receipts so it's immediately
            // accessible via /receipts/<filename>. This avoids relying on
            // storage symlinks on environments where that's inconvenient.
            try {
                $receiptsDir = public_path('receipts');
                if (!file_exists($receiptsDir)) mkdir($receiptsDir, 0755, true);
                $ext = $file->getClientOriginalExtension() ?: 'jpg';
                $filename = 'receipt_' . $procRequest->id . '_' . time() . '.' . $ext;
                $file->move($receiptsDir, $filename);
                // Store a public path starting with /receipts/... so frontend can use it directly
                $procRequest->receipt_path = '/receipts/' . $filename;
                $procRequest->receipt_uploaded_by = $user->id;
                $procRequest->receipt_uploaded_at = now();
                $procRequest->receipt_confirmed = false;
                $procRequest->save();
                Log::info('Receipt uploaded for procurement request', ['proc_req_id' => $procRequest->id, 'uploaded_by' => $user->id, 'path' => $procRequest->receipt_path]);
            } catch (\Exception $e) {
                Log::error('Failed to store receipt file in public/receipts', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Failed to save receipt file'], 500);
            }

            return response()->json(['ok' => true, 'message' => 'Receipt uploaded. Awaiting finance confirmation.', 'procurement_status' => 'receipt_submitted']);
        }

        // Without a receipt file, only allow completion if receipt was already
        // confirmed by finance. This enforces the flow: supplier uploads, finance confirms.
        if (empty($procRequest->receipt_path) || !$procRequest->receipt_confirmed) {
            return response()->json(['error' => 'Receipt not uploaded or not yet confirmed by finance'], 400);
        }

        // Instead of immediately incrementing product stock here, mark the
        // procurement request as awaiting inventory confirmation so inventory
        // staff can verify actual delivered quantities and confirm the stock
        // count. We store this in the `awaiting_inventory_confirmation` status
        // (added via migration) so the staff UI can surface the confirmation tile.
        try {
            DB::transaction(function () use ($procRequest, $user) {
                $procRequest->update([
                    'status' => 'awaiting_inventory_confirmation',
                    'procurement_user_id' => $user->id
                ]);

                // Do NOT increment product stock here; inventory staff will
                // confirm and perform the stock update. Also do not mark
                // supplier order fulfilled yet — that will be updated once
                // staff confirms delivered quantities.

                try {
                    $budgetReq = BudgetRequest::where('branch_id', $procRequest->branch_id)
                        ->where('purpose', 'LIKE', "%Procurement Request #{$procRequest->id}%")
                        ->first();

                    if ($budgetReq) {
                        $budgetReq->update([
                            'status' => 'Completed',
                            'processed_by' => $procRequest->procurement_user_id ?? $user->id,
                            'date_processed' => now()->toDateString(),
                        ]);
                        Log::info('Linked BudgetRequest marked Completed', ['budget_request_id' => $budgetReq->id, 'proc_req_id' => $procRequest->id]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to update linked BudgetRequest after procurement completion', ['error' => $e->getMessage(), 'proc_req_id' => $procRequest->id]);
                }
            });
        } catch (\Exception $e) {
            Log::error('Failed to mark procurement request awaiting stock confirmation', ['error' => $e->getMessage(), 'proc_req_id' => $procRequest->id]);
            return response()->json(['error' => 'Failed to update procurement status'], 500);
        }

        return response()->json(['ok' => true, 'message' => 'Procurement request marked awaiting stock confirmation', 'request' => $procRequest->fresh()->load('product')]);
    }

    /**
     * Finance confirms uploaded receipt and moves status to on_delivery.
     */
    public function confirmReceipt(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');
        $allowed = false;
        if (in_array($role, ['FINANCE_MANAGER', 'MANAGER_FINANCE']) || ($role === 'MANAGER' && $dept === 'FINANCE')) {
            $allowed = true;
        }
        if (!$allowed && $role === 'CUSTOM') {
            try {
                $perms = $user->permissions ?? [];
                if (is_string($perms)) $perms = json_decode($perms, true) ?: [];
                $modules = [];
                if (is_array($perms) && isset($perms['modules']) && is_array($perms['modules'])) {
                    $modules = $perms['modules'];
                } elseif (is_array($perms)) {
                    $modules = $perms;
                }
                foreach ($modules as $m) {
                    if (strtoupper(trim((string)$m)) === 'FINANCE') { $allowed = true; break; }
                }
            } catch (\Throwable $e) { /* ignore */ }
        }
        if (!$allowed) return response()->json(['error' => 'Unauthorized'], 401);

        $procRequest = ProcurementRequest::findOrFail($id);
        Log::info('confirmReceipt called', ['proc_req_id' => $id, 'user_id' => $user->id]);
        if (empty($procRequest->receipt_path)) {
            return response()->json(['error' => 'No receipt uploaded for this request'], 400);
        }

        try {
            DB::transaction(function () use ($procRequest, $user) {
                // Force update via query builder to avoid any stale model state issues
                DB::table('procurement_requests')
                    ->where('id', $procRequest->id)
                    ->update([
                        'receipt_confirmed' => true,
                        'receipt_confirmed_by' => $user->id,
                        'receipt_confirmed_at' => now(),
                        'status' => 'ongoing_delivery',
                        'updated_at' => now(),
                    ]);

                // Refresh model instance
                $procRequest->refresh();

                // if there's a linked supplier order, mark it on_delivery (guard if table exists)
                if (Schema::hasTable('supplier_orders')) {
                    try {
                        $supplierOrder = SupplierOrder::where('procurement_request_id', $procRequest->id)->first();
                        if ($supplierOrder) {
                            $supplierOrder->update(['status' => 'on_delivery']);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to update SupplierOrder status after receipt confirm', ['error' => $e->getMessage(), 'proc_req_id' => $procRequest->id]);
                    }
                } else {
                    Log::warning('supplier_orders table missing; skipping supplier order update', ['proc_req_id' => $procRequest->id]);
                }
            });
            Log::info('Receipt confirmed successfully', ['proc_req_id' => $procRequest->id, 'confirmed_by' => $user->id]);
        } catch (\Exception $e) {
            Log::error('Failed to confirm receipt', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'proc_req_id' => $procRequest->id]);
            return response()->json(['error' => 'Failed to confirm receipt', 'message' => $e->getMessage()], 500);
        }

        return response()->json(['ok' => true, 'message' => 'Receipt confirmed. Status set to ongoing_delivery.', 'request' => $procRequest->fresh()]);
    }

    /**
     * Get suppliers who have confirmed they have the product for a procurement request.
     * This is used when multiple suppliers submit products and procurement needs to choose one.
     * GET /api/procurement-requests/{id}/confirmed-suppliers
     */
    public function confirmedSuppliers(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $procRequest = ProcurementRequest::with('product')->findOrFail($id);

        // Get all SupplierOrders for this procurement request where supplier has confirmed (via submitProduct)
        // Only include orders that have product_id (meaning supplier confirmed availability)
        $supplierOrders = SupplierOrder::where('procurement_request_id', $procRequest->id)
            ->whereNotNull('product_id')
            ->with(['supplier', 'product'])
            ->get();

        if ($supplierOrders->isEmpty()) {
            return response()->json([
                'ok' => true,
                'confirmed_count' => 0,
                'suppliers' => []
            ]);
        }

        // Build supplier list with relevant info from SupplierOrder and linked products
        $suppliers = $supplierOrders->map(function ($order) {
            $supplier = $order->supplier;
            $product = $order->product;
            
            return [
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->full_name ?? $supplier->username,
                'supplier_username' => $supplier->username,
                'supplier_email' => $supplier->email,
                'supplier_phone' => $supplier->phone_number,
                'order_id' => $order->id,
                'order_status' => $order->status,
                'product_name' => $product?->name ?? 'Unknown',
                'product_price' => $product?->price ?? 0,
                'product_stock' => $product?->stock ?? 0,
                'product_expiry' => $product?->expires_at ?? null,
                'product_category' => $product?->category ?? 'Uncategorized',
                'per_pack_or_individual' => $product?->per_pack_or_individual,
            ];
        })->values();

        Log::info('confirmedSuppliers retrieved', [
            'proc_request_id' => $procRequest->id,
            'count' => $suppliers->count()
        ]);

        return response()->json([
            'ok' => true,
            'confirmed_count' => $suppliers->count(),
            'suppliers' => $suppliers
        ]);
    }
}

