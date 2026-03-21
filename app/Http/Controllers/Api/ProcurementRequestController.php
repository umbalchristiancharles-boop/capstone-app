<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProcurementRequest;
use App\Models\SupplierOrder;
use App\Models\Product;
use App\Models\BudgetRequest;
use Illuminate\Support\Facades\Log;

class ProcurementRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');

        $query = ProcurementRequest::with(['product', 'logisticsUser', 'procurementUser', 'financeUser'])
            ->orderBy('created_at', 'desc');

        // Allow Super Admin to view requests across branches (optionally filtered by branch_id)
        if ($role === 'SUPER_ADMIN') {
            $branchFilter = $request->query('branch_id');
            if ($branchFilter) {
                $query->where('branch_id', $branchFilter);
            }
        } elseif (in_array($role, ['LOGISTICS_MANAGER', 'MANAGER_LOGISTICS'])) {
            // Logistics sees own requests
            $query->where('logistics_user_id', $user->id);
        } elseif ($role === 'PROCUREMENT_MANAGER') {
            // Procurement sees pending/approved/budget/cash-in-transit/delivery states for branch
            $query->where('branch_id', $user->branch_id ?? 1)
                  ->whereIn('status', ['pending', 'approved', 'budget_pending', 'cash_in_transit', 'pending_order_to_supplier']);
        } elseif (in_array($role, ['FINANCE_MANAGER', 'MANAGER_FINANCE'])) {
            // Finance sees pending budget approvals and items they need to confirm (cash in transit)
            $query->where(function($q) {
                $q->where('budget_approved', false)
                  ->orWhere('status', 'cash_in_transit');
            });
        } else {
            // Default branch filter
            $query->where('branch_id', $user->branch_id ?? 1);
        }

        $requests = $query->paginate(20);

        return response()->json($requests);
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

        if (!(
            $role === 'LOGISTICS_MANAGER' || $role === 'MANAGER_LOGISTICS' ||
            ($role === 'MANAGER' && $dept === 'LOGISTICS') ||
            $role === 'SUPER_ADMIN'
        )) {
            Log::error('UNAUTHORIZED ROLE', ['role' => $role, 'dept' => $dept]);
            return response()->json(['error' => 'Unauthorized role'], 401);
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
        if (!($isProcurementManager || $isManagerProcurement || $isSuperAdmin)) {
            Log::warning('UNAUTHORIZED ROLE', ['role' => $role, 'dept' => $dept]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $branchId = $isSuperAdmin ? ($request->query('branch_id') ?? 1) : ($user->branch_id ?? 1);
        Log::info('Querying pending requests', ['branch_id' => $branchId]);

        try {
            $requests = ProcurementRequest::with(['product:id,name,price,sku,branch_id,supplier_id,logistics_request_available'])
                ->where('branch_id', $branchId)
                    ->whereIn('status', ['pending', 'budget_pending', 'pending_order_to_supplier', 'delivery_pending', 'ongoing_delivery'])
                ->get(['id', 'product_id', 'branch_id', 'status', 'budget_approved']);
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
            $products = $products->map(function ($p) use ($requestsByProduct) {
                $req = $requestsByProduct->get($p->id);
                $p->procurement_request_id = $req ? $req->id : null;
                $p->procurement_status = $req ? $req->status : null;
                $p->procurement_budget_approved = $req ? (bool)$req->budget_approved : false;
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

        // Allow either explicit PROCUREMENT_MANAGER role or a branch Manager in PROCUREMENT
        if (( $role === 'PROCUREMENT_MANAGER' || ($role === 'MANAGER' && $dept === 'PROCUREMENT') )
            && $procRequest->status === 'pending') {
            try {
                // Procurement acknowledges and auto-creates BudgetRequest for Finance panel
                DB::transaction(function () use ($procRequest, $user) {
                    $procRequest->update([
                        'procurement_user_id' => $user->id,
                        'status' => 'budget_pending'
                    ]);

                    // Check if BudgetRequest already exists for this procurement request
                    $existingBudget = BudgetRequest::where('branch_id', $procRequest->branch_id)
                        ->where('purpose', 'LIKE', "%Procurement Request #{$procRequest->id}%")
                        ->first();

                    if (!$existingBudget) {
                        BudgetRequest::create([
                            'branch_id' => $procRequest->branch_id,
                            'user_id' => $user->id, // procurement manager as requester
                            'purpose' => "Procurement Request #{$procRequest->id}: {$procRequest->product->name} x{$procRequest->quantity}",
                            'requested_amount' => $procRequest->total_amount,
                            'status' => 'Pending',
                            'date_requested' => now()->toDateString(),
                        ]);
                        
                        Log::info('Auto-created BudgetRequest from ProcurementRequest', [
                            'proc_req_id' => $procRequest->id,
                            'budget_user_id' => $user->id,
                            'branch_id' => $procRequest->branch_id
                        ]);
                    }
                });
                Log::info('Procurement acknowledgment successful', ['proc_req_id' => $procRequest->id]);
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
                // Finance confirms cash was given physically -> move to delivery pending
                        $procRequest->update([
                            'status' => 'pending_order_to_supplier'
                        ]);
            } else {
                return response()->json(['error' => 'No action available for this request'], 400);
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json($procRequest->fresh()->load(['product', 'logisticsUser', 'procurementUser', 'financeUser']));
    }

    public function completeOrder(Request $request, $id)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');
        if ($role !== 'PROCUREMENT_MANAGER' && !($role === 'MANAGER' && $dept === 'PROCUREMENT')) {
            Log::warning('UNAUTHORIZED ROLE', ['role' => $role, 'dept' => $dept]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $procRequest = ProcurementRequest::with('product')->findOrFail($id);
        // Allow procurement manager to mark a procurement request as completed
        // (delivery received) when appropriate. Acceptable current statuses
        // include orders that were sent to supplier or are on delivery.
        $allowedForComplete = ['pending_order_to_supplier', 'delivery_pending', 'on_delivery', 'ongoing_delivery'];

        if ($user->branch_id && $procRequest->branch_id != $user->branch_id) {
            return response()->json(['error' => 'Not your branch'], 403);
        }

        if (!in_array($procRequest->status, $allowedForComplete, true)) {
            return response()->json(['error' => 'Request not in a state that can be completed'], 400);
        }

        try {
            DB::transaction(function () use ($procRequest, $user) {
                // Update procurement request status to completed
                $procRequest->update([
                    'status' => 'completed',
                    'procurement_user_id' => $user->id
                ]);

                // Try to increment stock for the product (if present)
                if ($procRequest->product) {
                    $procRequest->product->increment('stock', $procRequest->quantity);
                    $procRequest->product->update(['has_been_ordered' => true, 'logistics_request_available' => false]);
                }

                // If there's a linked SupplierOrder, mark it fulfilled as well
                $supplierOrder = SupplierOrder::where('procurement_request_id', $procRequest->id)->first();
                if ($supplierOrder && $supplierOrder->status !== 'fulfilled') {
                    $supplierOrder->update(['status' => 'fulfilled', 'fulfilled_at' => now()]);
                }

                // If there's a linked BudgetRequest created for this procurement,
                // mark it as completed so the finance panel reflects the fulfilled request.
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
            Log::error('Failed to mark procurement request completed', ['error' => $e->getMessage(), 'proc_req_id' => $procRequest->id]);
            return response()->json(['error' => 'Failed to mark as completed'], 500);
        }

        return response()->json(['ok' => true, 'message' => 'Procurement request marked as completed', 'request' => $procRequest->fresh()->load('product')]);
    }
}

