<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProcurementRequest;
use Illuminate\Support\Facades\Log;

class ProcurementProductController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');
        $branchId = $user->branch_id;

        $isProcurementManager = $role === 'PROCUREMENT_MANAGER';
        $isManagerProcurement = ($role === 'MANAGER' && $dept === 'PROCUREMENT');
        if (!($isProcurementManager || $isManagerProcurement)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Log::info('PROCUREMENT PRODUCTS QUERY START', [
            'user_id' => $user->id,
            'role' => $role,
            'branch_id' => $branchId,
        ]);

        $query = Product::with(['supplier:id,username,full_name', 'procurementRequests' => function ($q) {
            $q->where('status', 'pending')->latest();
        }])
        ->where('is_active', 1);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $query->where(function ($q) {
            $q->where('is_published', true)
              ->orWhere('logistics_request_available', true)
              ->orWhere(function ($subQ) {
                  $subQ->whereNotNull('supplier_id')
                       ->orWhere(function ($t) {
                           $t->whereNotNull('supplier_name')
                             ->where('supplier_name', '<>', '');
                       })
                       ->where('is_published', false);
              });
        });

        $count = $query->count();
        Log::info('PROCUREMENT PRODUCTS QUERY COUNT: ' . $count . ', SQL: ' . $query->toSql(), $query->getBindings());

        $products = $query->get();

        Log::info('PROCUREMENT PRODUCTS FINAL COUNT', ['count' => $products->count()]);

        return response()->json($products);
    }

    public function requestedProducts(Request $request)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');
        $branchId = $user->branch_id;

        $isProcurementManager = $role === 'PROCUREMENT_MANAGER';
        $isManagerProcurement = ($role === 'MANAGER' && $dept === 'PROCUREMENT');
        if (!($isProcurementManager || $isManagerProcurement)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Log::info('PROCUREMENT REQUESTED PRODUCTS', [
            'user_id' => $user->id,
            'role' => $role,
            'branch_id' => $branchId,
        ]);

        $products = Product::with(['supplier:id,username,full_name'])
            ->where('is_active', 1)
            ->whereHas('procurementRequests', function ($q) use ($branchId) {
                $q->where('status', 'pending');
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            });

        if ($branchId) {
            $products->where('branch_id', $branchId);
        }

        $products = $products->get();

        return response()->json($products);
    }

    public function placeOrder(Request $request, $productId)
    {
        $user = $request->user();
        $role = strtoupper($user->role ?? '');
        $dept = strtoupper($user->department ?? '');
        $isProcurementManager = $role === 'PROCUREMENT_MANAGER';
        $isManagerProcurement = ($role === 'MANAGER' && $dept === 'PROCUREMENT');
        if (!($isProcurementManager || $isManagerProcurement)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'quantity' => 'integer|min:1'
        ]);

        $product = Product::findOrFail($productId);
        if ($user->branch_id && $product->branch_id != $user->branch_id) {
            return response()->json(['error' => 'Not your branch'], 403);
        }

        $quantity = $validated['quantity'] ?? 1;

        // Find pending procurement request or create new logic if needed
        $procRequest = ProcurementRequest::where('product_id', $productId)
            ->where('status', 'pending')
            ->where('branch_id', $user->branch_id ?? 1)
            ->first();

        if (!$procRequest) {
            return response()->json(['error' => 'No pending logistics request for this product'], 400);
        }

        // Use existing completeOrder logic
        return app(ProcurementRequestController::class)->completeOrder($request, $procRequest->id);
    }
}
