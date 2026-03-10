<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    /**
     * List active branches.
     */
    public function branches()
    {
        return response()->json(Branch::where('is_active', true)->get());
    }

    /**
     * List products for a given branch (with stock > 0).
     * Uses authenticated user's branch_id to prevent cross-branch access.
     */
    public function products(Request $request)
    {
        $user = $request->user();

        // Determine branch_id - use authenticated user's branch, or allow OWNER/SUPER_ADMIN to view all
        $branchId = null;

        if ($user && in_array($user->role, ['OWNER', 'SUPER_ADMIN', 'SUPERADMIN'])) {
            // Owners and super admins can view all branches if they specify one
            $branchId = $request->filled('branch_id') ? $request->branch_id : null;
        } elseif ($user && $user->branch_id) {
            // Regular users can only see their own branch
            $branchId = $user->branch_id;
        } else {
            // No branch assigned - return empty
            return response()->json([]);
        }

        $query = Product::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return response()->json(
            $query->orderBy('name')->get()
        );
    }

    /**
     * Process a cashier transaction: create order + order items, deduct stock.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'branch_id'           => 'required|exists:branches,id',
            'customer_name'       => 'nullable|string|max:255',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'amount_paid'         => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $grandTotal = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('branch_id', $request->branch_id)
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    abort(422, "Product #{$item['product_id']} not found in this branch.");
                }

                if ($product->stock < $item['quantity']) {
                    abort(422, "Insufficient stock for {$product->name}. Available: {$product->stock}");
                }

                $subtotal = $product->price * $item['quantity'];
                $grandTotal += $subtotal;

                $orderItems[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'unit_price'   => $product->price,
                    'quantity'     => $item['quantity'],
                    'subtotal'     => $subtotal,
                ];

                // Deduct stock
                $product->decrement('stock', $item['quantity']);
            }

            $amountPaid = (float) $request->amount_paid;

            if ($amountPaid < $grandTotal) {
                abort(422, 'Insufficient payment. Total is ₱' . number_format($grandTotal, 2));
            }

            $changeAmount = $amountPaid - $grandTotal;

            // Generate order code
            $lastOrder = Order::orderByDesc('id')->first();
            $nextNum = $lastOrder ? ((int) str_replace('CT-', '', $lastOrder->order_code)) + 1 : 1;
            $orderCode = 'CT-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            $order = Order::create([
                'order_code'    => $orderCode,
                'owner_id'      => $request->user()->id,
                'cashier_id'    => $request->user()->id,
                'branch_id'     => $request->branch_id,
                'customer_name' => $request->customer_name ?? 'Walk-in',
                'status'        => 'completed',
                'grand_total'   => $grandTotal,
                'amount_paid'   => $amountPaid,
                'change_amount' => $changeAmount,
                'ordered_at'    => now(),
            ]);

            foreach ($orderItems as $oi) {
                $order->items()->create($oi);
            }

            $order->load('items', 'branch');

            return response()->json([
                'ok'      => true,
                'message' => 'Transaction completed!',
                'order'   => $order,
                'change'  => $changeAmount,
            ]);
        });
    }

    /**
     * Recent transactions for the cashier view.
     * Uses authenticated user's branch_id to prevent cross-branch access.
     */
    public function transactions(Request $request)
    {
        $user = $request->user();

        // Determine branch_id - use authenticated user's branch, or allow OWNER/SUPER_ADMIN to view all
        $branchId = null;

        if ($user && in_array($user->role, ['OWNER', 'SUPER_ADMIN', 'SUPERADMIN'])) {
            // Owners and super admins can view all branches if they specify one
            $branchId = $request->filled('branch_id') ? $request->branch_id : null;
        } elseif ($user && $user->branch_id) {
            // Regular users can only see their own branch
            $branchId = $user->branch_id;
        } else {
            // No branch assigned - return empty
            return response()->json([]);
        }

        $query = Order::with('items', 'branch')
            ->orderByDesc('ordered_at');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return response()->json($query->limit(50)->get());
    }
}
