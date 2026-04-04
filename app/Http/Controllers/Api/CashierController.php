<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Dish;
use App\Models\DishIngredient;
use App\Models\PriceMarkupPercentage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CashierController extends Controller
{
    /**
     * Get the price markup multiplier for a branch
     * E.g., 20% markup returns 1.20
     */
    private function getMarkupMultiplier($branchId): float
    {
        $markup = PriceMarkupPercentage::where('branch_id', $branchId)
            ->where('is_active', true)
            ->first();

        if (!$markup) {
            // Default to 20% if not configured
            return 1.20;
        }

        return $markup->getMultiplier();
    }

    /**
     * Compute available servings and cost for a given Dish by aggregating
     * candidate ingredient products in the branch (handles SKU/name duplicates
     * and pack-aware quantities).
     *
     * @param \App\Models\Dish $dish
     * @param int $branchId
     * @return array [int $maxServings, float $costSum]
     */
    private function computeDishAvailability($dish, $branchId)
    {
        $costSum = 0.0;
        $maxServings = null;

        foreach ($dish->ingredients as $ing) {
            $perServing = (float) ($ing->per_serving ?? 1);
            if ($perServing <= 0) $perServing = 1;

            $nameRaw = trim((string) $ing->name);
            $nameUpper = strtoupper($nameRaw);
            $normalized = preg_replace('/[^A-Z0-9]+/', '', $nameUpper);
            $skuKey = $ing->product?->sku ?? null;

            $candidateQuery = Product::where('branch_id', $branchId)->where('is_active', 1)
                ->where(function ($q) use ($skuKey, $nameRaw) {
                    if ($skuKey) $q->orWhere('sku', $skuKey);
                    $q->orWhere('name', 'like', '%' . str_replace(' ', '%', $nameRaw) . '%');
                });

            $candidateProducts = $candidateQuery->get();

            if ($candidateProducts->isEmpty()) {
                // missing ingredient -> zero availability
                return [0, 0.0];
            }

            $filtered = $candidateProducts->filter(function ($p) use ($normalized) {
                $pn = strtoupper($p->name ?? '');
                $pnNorm = preg_replace('/[^A-Z0-9]+/', '', $pn);
                if ($pnNorm === $normalized) return true;
                if (soundex($pn) === soundex($normalized)) return true;
                return false;
            });

            if ($filtered->isNotEmpty()) {
                $candidateProducts = $filtered;
            }

            $totalPiecesAvailable = 0;
            $totalCost = 0.0;
            $isCondiment = false;

            foreach ($candidateProducts as $cp) {
                $cat = strtolower(trim($cp->category ?? ''));
                if ($cat === 'condiment') {
                    $isCondiment = true;
                }

                $perPackModeCp = in_array($cp->per_pack_or_individual, ['per_pack', 'both']);
                $packQtyCp = (float) ($cp->pack_quantity ?? 0);
                if ($perPackModeCp && $packQtyCp > 0) {
                    $openUsedCp = (float) ($cp->open_pack_used ?? 0);
                    $totalPiecesAvailable += (($cp->stock ?? 0) * $packQtyCp) - $openUsedCp;
                } else {
                    $totalPiecesAvailable += (float) ($cp->stock ?? 0);
                }

                $totalCost += ((float) ($cp->cost_price ?? $cp->price ?? 0)) * 1;
            }

            if ($isCondiment && $totalPiecesAvailable <= 0) {
                $costSum += ($totalCost * $perServing);
                continue;
            }

            $possibleByIng = (int) floor($totalPiecesAvailable / max(1, $perServing));
            $maxServings = is_null($maxServings) ? $possibleByIng : min($maxServings, $possibleByIng);
            $costSum += ($totalCost * $perServing);
        }

        return [(int) ($maxServings ?? 0), (float) $costSum];
    }

    /**
     * Consume required pieces for an ingredient across candidate products in a branch.
     * Returns true if consumption succeeded fully, false if insufficient.
     * This handles per-pack products (updates stock + open_pack_used) and individual units.
     */
    private function consumeIngredientProducts($ing, $branchId, int $requiredPieces): bool
    {
        if ($requiredPieces <= 0) return true;

        $nameRaw = trim((string) $ing->name);
        $nameUpper = strtoupper($nameRaw);
        $normalized = preg_replace('/[^A-Z0-9]+/', '', $nameUpper);
        $skuKey = $ing->product?->sku ?? null;

        $candidateQuery = Product::where('branch_id', $branchId)->where('is_active', 1)
            ->where(function ($q) use ($skuKey, $nameRaw) {
                if ($skuKey) $q->orWhere('sku', $skuKey);
                $q->orWhere('name', 'like', '%' . str_replace(' ', '%', $nameRaw) . '%');
            });

        $candidates = $candidateQuery->lockForUpdate()->get();
        if ($candidates->isEmpty()) return false;

        // prefer exact normalized matches, then soundex, then others
        $sorted = $candidates->sortBy(function ($p) use ($normalized) {
            $pn = strtoupper($p->name ?? '');
            $pnNorm = preg_replace('/[^A-Z0-9]+/', '', $pn);
            if ($pnNorm === $normalized) return 0;
            if (soundex($pn) === soundex($normalized)) return 1;
            return 2;
        });

        $needed = $requiredPieces;
        foreach ($sorted as $cp) {
            $cat = strtolower(trim($cp->category ?? ''));
            if ($cat === 'condiment') {
                // do not consume condiments
                continue;
            }

            $perPackMode = in_array($cp->per_pack_or_individual, ['per_pack', 'both']);
            $packQty = (float) ($cp->pack_quantity ?? 0);

            $openUsed = (float) ($cp->open_pack_used ?? 0);
            $piecesAvailable = $perPackMode && $packQty > 0
                ? (($cp->stock ?? 0) * $packQty) - $openUsed
                : (int) ($cp->stock ?? 0);

            if ($piecesAvailable <= 0) continue;

            $toTake = min($needed, (int) $piecesAvailable);

            if ($perPackMode && $packQty > 0) {
                $totalAfter = $openUsed + $toTake;
                $packsToConsume = (int) floor($totalAfter / $packQty);
                $remainingOpenUsed = $totalAfter - ($packsToConsume * $packQty);

                if ($packsToConsume > 0) {
                    $dec = min($packsToConsume, $cp->stock);
                    $cp->decrement('stock', $dec);
                }

                $cp->open_pack_used = $remainingOpenUsed;
                $cp->save();
            } else {
                // individual units
                $dec = min((int) $cp->stock, $toTake);
                $cp->decrement('stock', $dec);
            }

            $needed -= $toTake;
            if ($needed <= 0) return true;
        }

        // not enough pieces across candidates — flag logistics for those products
        foreach ($candidates as $c) {
            try {
                $c->update(['logistics_request_available' => true]);
            } catch (\Exception $e) {
                // ignore
            }
        }

        return false;
    }

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

        // Product IDs used as ingredients for this branch (exclude from cashier as raw items)
        // Only consider ingredients from approved dishes so pending recipes don't hide regular products
        $ingredientIds = DishIngredient::whereNotNull('product_id')
            ->whereHas('dish', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->where('approval_status', 'approved');
            })
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Also exclude ingredient names (case-insensitive) for ingredients that may not have product_id linked
        $ingredientNames = DishIngredient::whereHas('dish', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->where('approval_status', 'approved');
            })
            ->pluck('name')
            ->filter()
            ->map(fn($n) => trim(strtoupper((string) $n)))
            ->unique()
            ->values()
            ->all();

        // 1) Regular sellable products (non-dish, not ingredient, stock > 0)
        $regularProductsQuery = Product::query()
            ->where('is_active', 1)
            ->where('is_published', 1)
            ->where('branch_id', $branchId)
            ->where('stock', '>', 0)
            ->where(function ($q) {
                $q->whereNull('is_kitchen_dish')->orWhere('is_kitchen_dish', false);
            });

        if (!empty($ingredientIds)) {
            $regularProductsQuery->whereNotIn('id', $ingredientIds);
        }

        if (!empty($ingredientNames)) {
            $regularProductsQuery->whereNotIn(DB::raw('TRIM(UPPER(name))'), $ingredientNames);
        }

        $out = $regularProductsQuery->orderBy('name')->get()->toArray();

        // 2) Dishes from dishes table with computed price/cost and computed available servings
        // Show only approved dishes; older records with null status are still allowed
        $dishes = Dish::where('branch_id', $branchId)
            ->where('approval_status', 'approved')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'active');
            })
            ->with(['ingredients.product'])
            ->orderBy('name')
            ->get();

            foreach ($dishes as $dish) {
            [$maxServings, $costSum] = $this->computeDishAvailability($dish, $branchId);

            $markup = $this->getMarkupMultiplier($branchId);
            $sellingPrice = $costSum > 0 ? round($costSum * $markup, 2) : null;

            // Ensure a dish product exists so checkout can keep using product_id
            $dishProduct = Product::where('branch_id', $branchId)
                ->whereRaw('TRIM(UPPER(name)) = ?', [trim(strtoupper($dish->name))])
                ->first();

            if (!$dishProduct) {
                $skuBase = strtoupper(substr(preg_replace('/[^A-Z0-9]+/i', '', $dish->name), 0, 8));
                if ($skuBase === '') {
                    $skuBase = 'DISH';
                }
                do {
                    $sku = $skuBase . '-' . strtoupper(Str::random(4));
                } while (Product::where('sku', $sku)->exists());

                $dishProduct = Product::create([
                    'name' => $dish->name,
                    'slug' => Str::slug($dish->name),
                    'price' => $sellingPrice,
                    'cost_price' => round($costSum, 2),
                    'stock' => $maxServings,
                    'min_stock' => 0,
                    'sku' => $sku,
                    'branch_id' => $branchId,
                    // Newly created dish products remain unpublished until admin publishes
                    'is_published' => 0,
                    'dish_id' => $dish->id,
                    'is_dish_product' => true,
                    'has_been_ordered' => 0,
                    'is_active' => 1,
                    'is_kitchen_dish' => true,
                    'supplier_name' => null,
                    'supplier_id' => null,
                    'logistics_request_available' => false,
                ]);
            } else {
                $dishProduct->is_kitchen_dish = true;
                $dishProduct->is_active = true;
                $dishProduct->stock = $maxServings;
                // update stored price only when we have a computed selling price
                if (!is_null($sellingPrice)) {
                    $dishProduct->price = $sellingPrice;
                }
                if ($costSum > 0) {
                    $dishProduct->cost_price = round($costSum, 2);
                }
                $dishProduct->save();
            }
            // Only expose dish products to the cashier if the representative product is published
            if (empty($dishProduct->is_published) || !$dishProduct->is_published) {
                continue;
            }

            // Prepare row: prefer computed selling price if available, otherwise use stored product price
            $row = $dishProduct->toArray();
            $row['price'] = !is_null($sellingPrice) ? $sellingPrice : ($dishProduct->price ?? 0);
            $row['computed_cost'] = $costSum > 0 ? round($costSum, 2) : ($dishProduct->cost_price ?? null);
            // Use computed available servings when >0, otherwise fall back to stored product stock
            $row['stock'] = $maxServings > 0 ? $maxServings : ($dishProduct->stock ?? 0);
            $row['is_kitchen_dish'] = true;
            $out[] = $row;
        }

        usort($out, function ($a, $b) {
            return strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });

        return response()->json($out);
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
                    ->where('is_published', 1)
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    abort(422, "Product #{$item['product_id']} not found in this branch.");
                }

                $unitPrice = (float) $product->price;

                // If this is a kitchen dish, compute availability and cost using shared helper
                if ($product->is_kitchen_dish) {
                    $dish = Dish::whereRaw('TRIM(UPPER(name)) = ?', [trim(strtoupper($product->name))])
                        ->where('branch_id', $request->branch_id)
                        ->with(['ingredients.product'])
                        ->first();

                    if (!$dish) {
                        $possible = Dish::where('branch_id', $request->branch_id)->with(['ingredients.product'])->get();
                        $pn = trim(strtoupper($product->name));
                        foreach ($possible as $pd) {
                            $dn = trim(strtoupper($pd->name));
                            if ($dn !== '' && (strpos($pn, $dn) !== false || strpos($dn, $pn) !== false)) {
                                $dish = $pd;
                                break;
                            }
                        }
                    }

                    if (!$dish) {
                        abort(422, "Dish definition for {$product->name} not found.");
                    }

                    [$maxServings, $costSum] = $this->computeDishAvailability($dish, $request->branch_id);

                    if ($maxServings <= 0) {
                        abort(422, "Insufficient ingredients/stock for {$product->name}.");
                    }

                    $markup = $this->getMarkupMultiplier($request->branch_id);
                    $sellingPrice = $costSum > 0 ? round($costSum * $markup, 2) : (float) $product->price;
                    $unitPrice = $sellingPrice;
                    $subtotal = $sellingPrice * $item['quantity'];
                } else {
                    if ($product->stock < $item['quantity']) {
                        abort(422, "Insufficient stock for {$product->name}. Available: {$product->stock}");
                    }

                    $subtotal = $product->price * $item['quantity'];
                }
                $grandTotal += $subtotal;

                $orderItems[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'unit_price'   => $unitPrice,
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

            // Check if order contains any kitchen dishes
            $hasKitchenDishes = false;
            foreach ($orderItems as $oi) {
                $product = Product::find($oi['product_id']);
                if ($product && $product->is_kitchen_dish) {
                    $hasKitchenDishes = true;
                    break;
                }
            }

            // If order has kitchen dishes, set status to 'in_kitchen' so kitchen staff can see it
            $orderStatus = $hasKitchenDishes ? 'in_kitchen' : 'completed';

            $order = Order::create([
                'order_code'    => $orderCode,
                'owner_id'      => $request->user()->id,
                'cashier_id'    => $request->user()->id,
                'branch_id'     => $request->branch_id,
                'customer_name' => $request->customer_name ?? 'Walk-in',
                // If order contains kitchen dishes, mark as 'in_kitchen' for kitchen staff to process
                // Otherwise, mark as 'completed' for regular items
                'status'        => $orderStatus,
                'is_cancelled'  => false,
                'subtotal'      => $subtotalAll,
                'discount_type' => $discountType,
                'discount_percent' => $discountPercent,
                'discount_amount' => round($discountAmount, 2),
                'vat_percent'   => $vatPercent * 100,
                'vat_amount'    => round($vatAmount, 2),
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

                if (!$prod) {
                    continue;
                }

                if ($prod->is_kitchen_dish) {
                    // decrement each ingredient according to per_serving * quantity
                    $dish = Dish::whereRaw('TRIM(UPPER(name)) = ?', [trim(strtoupper($prod->name))])
                        ->where('branch_id', $request->branch_id)
                        ->with('ingredients')
                        ->first();

                    if (!$dish) {
                        $possible = Dish::where('branch_id', $request->branch_id)->with('ingredients')->get();
                        $pn = trim(strtoupper($prod->name));
                        foreach ($possible as $pd) {
                            $dn = trim(strtoupper($pd->name));
                            if ($dn !== '' && (strpos($pn, $dn) !== false || strpos($dn, $pn) !== false)) {
                                $dish = $pd;
                                break;
                            }
                        }
                    }

                        if ($dish) {
                                foreach ($dish->ingredients as $ing) {
                                    $required = (float) ($ing->per_serving ?? 1);
                                    if ($required <= 0) $required = 1;
                                    $required = $required * $it->quantity;

                                    $consumed = $this->consumeIngredientProducts($ing, $request->branch_id, (int) $required);

                                    if (! $consumed) {
                                        // Flag logistics for representative product if present, otherwise flag candidates
                                        if ($ing->product_id) {
                                            try {
                                                Product::where('id', $ing->product_id)
                                                    ->where('branch_id', $request->branch_id)
                                                    ->update(['logistics_request_available' => true]);
                                            } catch (\Exception $e) {
                                                // ignore
                                            }
                                        } else {
                                            // best-effort: flag products that match the ingredient name
                                            $nameRaw = trim((string) $ing->name);
                                            Product::where('branch_id', $request->branch_id)
                                                ->where('name', 'like', '%' . str_replace(' ', '%', $nameRaw) . '%')
                                                ->update(['logistics_request_available' => true]);
                                        }
                                    }
                                }
                        }

                    // Optionally decrement the dish product stock if tracked (pack-aware)
                    if (!is_null($prod->stock)) {
                        $perPackModeProd = in_array($prod->per_pack_or_individual, ['per_pack', 'both']);
                        $packQtyProd = (float) ($prod->pack_quantity ?? 0);

                        if ($perPackModeProd && $packQtyProd > 0) {
                            $openUsedProd = (float) ($prod->open_pack_used ?? 0);
                            $requiredPiecesProd = (float) $it->quantity;
                            $totalPiecesAvailableProd = ($prod->stock * $packQtyProd) - $openUsedProd;

                            if ($totalPiecesAvailableProd >= $requiredPiecesProd) {
                                $totalAfterProd = $openUsedProd + $requiredPiecesProd;
                                $packsToConsumeProd = (int) floor($totalAfterProd / $packQtyProd);
                                $remainingOpenUsedProd = $totalAfterProd - ($packsToConsumeProd * $packQtyProd);

                                if ($packsToConsumeProd > 0) {
                                    $prod->decrement('stock', $packsToConsumeProd);
                                }

                                $prod->open_pack_used = $remainingOpenUsedProd;
                                $prod->save();
                            } else {
                                $prod->update(['logistics_request_available' => true]);
                            }
                        } else {
                            $prod->stock = max(0, $prod->stock - $it->quantity);
                            $prod->save();
                        }
                    }
                } else {
                    // Regular product sold directly: apply pack-aware consumption
                    $perPackMode = in_array($prod->per_pack_or_individual, ['per_pack', 'both']);
                    $packQty = (float) ($prod->pack_quantity ?? 0);

                    if ($perPackMode && $packQty > 0) {
                        $openUsed = (float) ($prod->open_pack_used ?? 0);
                        $requiredPieces = (float) $it->quantity;
                        $totalPiecesAvailable = ($prod->stock * $packQty) - $openUsed;

                        if ($totalPiecesAvailable >= $requiredPieces) {
                            $totalAfter = $openUsed + $requiredPieces;
                            $packsToConsume = (int) floor($totalAfter / $packQty);
                            $remainingOpenUsed = $totalAfter - ($packsToConsume * $packQty);

                            if ($packsToConsume > 0) {
                                $prod->decrement('stock', $packsToConsume);
                            }

                            $prod->open_pack_used = $remainingOpenUsed;
                            $prod->save();
                        } else {
                            // insufficient pieces across packs
                            $prod->update(['logistics_request_available' => true]);
                        }
                    } else {
                        $newStock = max(0, $prod->stock - $it->quantity);
                        $prod->stock = $newStock;
                        $prod->save();
                    }
                }
            }

            $order->approved_at = now();
            $order->approved_by = $request->user()->id;
            if ($order->status !== 'in_kitchen') {
                $order->status = 'completed';
            }
            $order->save();
            $order->load('items', 'branch');

            // Update branch budget to reflect this cashier transaction (credit sales to budget)
            $branch = Branch::where('id', $request->branch_id)->lockForUpdate()->first();
            if ($branch) {
                $amount = round($finalGrandTotal, 2);
                $branch->budget = is_null($branch->budget) ? (float) $amount : ($branch->budget + (float) $amount);
                $branch->save();
            }

            return response()->json([
                'ok'      => true,
                'message' => 'Order completed successfully.',
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
     * Refund a completed order from cashier UI.
     * This marks the order as cancelled/refunded, records a refund reason,
     * updates the branch budget (subtracts the order amount), and does NOT
     * restock inventory (items are treated as disposed).
     */
    public function refund(Request $request)
    {
        $request->validate([
            'order_code' => 'required|string',
            'branch_id'  => 'required|exists:branches,id',
            'reason'     => 'required|string|max:1000',
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($request, $user) {
            $order = Order::where('order_code', $request->order_code)
                ->where('branch_id', $request->branch_id)
                ->where('is_cancelled', false)
                ->whereIn('status', ['completed', 'approved'])
                ->with('items')
                ->first();

            if (!$order) {
                return response()->json(['error' => 'Order not found or already refunded/cancelled.'], 404);
            }

            // Allow OWNER / SUPER_ADMIN to refund any order; otherwise only the cashier who created it
            if (!in_array($user->role, ['OWNER', 'SUPER_ADMIN', 'SUPERADMIN']) && $order->cashier_id !== $user->id) {
                return response()->json(['error' => 'Not authorized to refund this order.'], 403);
            }

            // Mark as cancelled/refunded and save reason. Do NOT restock products.
            $order->status = 'cancelled';
            $order->is_cancelled = true;
            $order->cancelled_at = now();
            $order->cancelled_by = $user->id;
            $order->refund_reason = $request->reason;
            $order->save();

            // Update branch budget: subtract the refunded amount.
            $branch = Branch::where('id', $request->branch_id)->lockForUpdate()->first();
            if ($branch) {
                $amount = (float) $order->grand_total;
                $branch->budget = is_null($branch->budget) ? -$amount : ($branch->budget - $amount);
                $branch->save();
            }

            return response()->json([
                'ok' => true,
                'message' => 'Order refunded successfully.',
                'order' => $order->fresh(),
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
            ->whereIn('status', ['pending', 'in_kitchen', 'approved', 'completed', 'cancelled'])
            ->orderByDesc('ordered_at');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return response()->json($query->limit(50)->get());
    }
}
