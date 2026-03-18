<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProcurementRequest;
use App\Models\SupplierOrder;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProcurementRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');

        $query = ProcurementRequest::with(['product', 'logisticsUser', 'procurementUser', 'financeUser'])
            ->orderBy('created_at', 'desc');

        if (in_array($role, ['LOGISTICS_MANAGER', 'MANAGER_LOGISTICS'])) {
            // Logistics sees own requests
            $query->where('logistics_user_id', $user->id);
        } elseif ($role === 'PROCUREMENT_MANAGER') {
            // Procurement sees pending/approved for branch
            $query->where('branch_id', $user->branch_id ?? 1)
                  ->whereIn('status', ['pending', 'approved', 'budget_pending']);
        } elseif (in_array($role, ['FINANCE_MANAGER', 'MANAGER_FINANCE'])) {
            // Finance sees pending budget approvals
            $query->where('budget_approved', false);
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
            ($role === 'MANAGER' && $dept === 'LOGISTICS')
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

        if ($user->branch_id && $product->branch_id != $user->branch_id) {
            Log::error('BRANCH MISMATCH', ['user_branch' => $user->branch_id, 'product_branch' => $product->branch_id]);
            return response()->json(['error' => 'Product not in your branch'], 403);
        }

        $branchId = $user->branch_id ?: 1;
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
            $product->update(['logistics_request_available' => true]);
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
        if (!($isProcurementManager || $isManagerProcurement)) {
            Log::warning('UNAUTHORIZED ROLE', ['role' => $role, 'dept' => $dept]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $branchId = $user->branch_id ?? 1;
        Log::info('Querying pending requests', ['branch_id' => $branchId]);

        try {
            $requests = ProcurementRequest::with(['product:id,name,price,sku,branch_id,supplier_id,logistics_request_available'])
                ->where('branch_id', $branchId)
                ->where('status', 'pending')
                ->get(['id', 'product_id', 'branch_id']);
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
        $user = $request->user();
        $role = strtoupper($user->role ?? '');

        $procRequest = ProcurementRequest::findOrFail($id);

        if ($role === 'PROCUREMENT_MANAGER' && $procRequest->status === 'pending') {
            $procRequest->update([
                'procurement_user_id' => $user->id,
                'status' => 'budget_pending'  // Wait for finance
            ]);
        } elseif (in_array($role, ['FINANCE_MANAGER', 'MANAGER_FINANCE']) && !$procRequest->budget_approved) {
            $validated = $request->validate(['budget_amount' => 'required|numeric|min:0']);
            if ($validated['budget_amount'] < $procRequest->total_amount) {
                return response()->json(['error' => 'Budget must cover total amount'], 400);
            }
            $procRequest->update([
                'finance_user_id' => $user->id,
                'budget_amount' => $validated['budget_amount'],
                'budget_approved' => true,
            ]);
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

        // Check prerequisites
        if ($procRequest->status !== 'budget_pending' || !$procRequest->budget_approved) {
            return response()->json(['error' => 'Budget must be approved first'], 400);
        }
        if ($user->branch_id && $procRequest->branch_id != $user->branch_id) {
            return response()->json(['error' => 'Not your branch'], 403);
        }

        // Update stock
        $procRequest->product->increment('stock', $procRequest->quantity);
        $procRequest->update(['status' => 'completed', 'procurement_user_id' => $user->id]);

        // Create supplier order
        SupplierOrder::create([
            'procurement_request_id' => $procRequest->id,
            'product_id' => $procRequest->product_id,
            'supplier_id' => $procRequest->product->supplier_id,
            'quantity' => $procRequest->quantity,
            'status' => 'pending',
            'branch_id' => $procRequest->branch_id,
        ]);

        return response()->json([
            'message' => 'Order completed successfully. Stock updated and supplier notified.',
            'request' => $procRequest->fresh()->load('product')
        ]);
    }
}

