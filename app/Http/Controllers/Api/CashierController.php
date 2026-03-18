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
     * Process a cashier transaction: create PENDING order + order items (NO stock deduction).
     * Finance approves → deduct stock + 'approved'.
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
            'discount_type'       => 'nullable|string|in:none,discount,pwd,senior',
            'discount_percent'    => 'nullable|numeric|min:0|max:100',
        ]);

        return DB::transaction(function () use ($request) {
            $grandTotal = 0;
            $orderItems = [];

            // Validate stock availability (but DON'T deduct yet - pending approval)
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
            }

            // compute discount and VAT
            $subtotalAll = $grandTotal;

            $discountType = $request->input('discount_type', 'none');
            $discountPercent = 0;
            if ($discountType === 'pwd') {
                $discountPercent = config('chikintayo.pwd_discount_percent', 0.20) * 100;
            } elseif ($discountType === 'senior') {
                $discountPercent = config('chikintayo.senior_discount_percent', 0.20) * 100;
            } elseif ($discountType === 'discount') {
                $discountPercent = (float) $request->input('discount_percent', 0);
            }

            $discountPercent = max(0, min(100, (float) $discountPercent));
            $discountAmount = ($subtotalAll * $discountPercent) / 100.0;

            // taxable amount after discount
            $taxable = max(0, $subtotalAll - $discountAmount);
            $vatPercent = (float) config('chikintayo.vat_percent', 0.12);
            $vatAmount = $taxable * $vatPercent;

            $finalGrandTotal = $taxable + $vatAmount;

            $amountPaid = (float) $request->amount_paid;

            if ($amountPaid < $finalGrandTotal) {
                abort(422, 'Insufficient payment. Total is ₱' . number_format($finalGrandTotal, 2));
            }

            $changeAmount = $amountPaid - $finalGrandTotal;

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
                // Cashier transactions are completed immediately — no finance approval required.
                'status'        => 'completed',
                'is_cancelled'  => false,
                'grand_total'   => $finalGrandTotal,
                'amount_paid'   => $amountPaid,
                'change_amount' => $changeAmount,
                'ordered_at'    => now(),
            ]);

            foreach ($orderItems as $oi) {
                $order->items()->create($oi);
            }

            // Deduct stock for each ordered item immediately so inventory reflects the transaction.
            // We lock the product rows again to ensure consistency within this transaction.
            foreach ($order->items as $it) {
                $prod = Product::where('id', $it->product_id)
                    ->where('branch_id', $request->branch_id)
                    ->lockForUpdate()
                    ->first();

                if ($prod) {
                    $newStock = max(0, $prod->stock - $it->quantity);
                    $prod->stock = $newStock;
                    $prod->save();
                }
            }

            // mark order as approved/completed with approval metadata
            $order->approved_at = now();
            $order->approved_by = $request->user()->id;
            $order->status = 'completed';
            $order->save();
            $order->load('items', 'branch');

            // include computed VAT and discount details in response (not persisted)
            $order->subtotal = $subtotalAll;
            $order->discount_type = $discountType;
            $order->discount_percent = $discountPercent;
            $order->discount_amount = round($discountAmount, 2);
            $order->vat_percent = $vatPercent * 100;
            $order->vat_amount = round($vatAmount, 2);
            $order->grand_total = round($finalGrandTotal, 2);

            return response()->json([
                'ok'      => true,
            'message' => 'Pending order created! Notify finance for approval.',
                'order'   => $order,
                'change'  => round($changeAmount, 2),
            ]);
        });
    }

    /**
     * Cancel pending order by cashier_id + branch_id + recent order_code
     */
    public function cancelPending(Request $request)
    {
        $request->validate([
            'order_code' => 'required|string',
            'branch_id'  => 'required|exists:branches,id',
        ]);

        $user = $request->user();

        $order = Order::where('order_code', $request->order_code)
            ->where('branch_id', $request->branch_id)
            ->where('cashier_id', $user->id)
            ->where('status', 'pending')
            ->where('is_cancelled', false)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Pending order not found or already cancelled.'], 404);
        }

        $order->update([
            'status' => 'cancelled',
            'is_cancelled' => true,
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Order cancelled successfully.',
            'order' => $order->fresh(),
        ]);
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
            ->whereIn('status', ['pending', 'approved', 'completed'])
            ->orderByDesc('ordered_at');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return response()->json($query->limit(50)->get());
    }
}
