<?php

namespace App\Services;

use App\Models\LogisticsTransaction;
use App\Models\ProcurementRequest;
use App\Models\SupplierOrder;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogisticsService
{
    /**
     * Create a new logistics transaction for procurement
     */
    public function createProcurementTransaction($procurementRequest, $type = 'procurement', $userId = null)
    {
        return DB::transaction(function () use ($procurementRequest, $type, $userId) {
            $existingTransaction = LogisticsTransaction::where('procurement_request_id', $procurementRequest->id)
                ->where('type', $type)
                ->where('status', 'pending')
                ->first();

            if ($existingTransaction && !$existingTransaction->is_duplicate) {
                Log::warning('Logistics transaction already exists for this procurement', [
                    'procurement_id' => $procurementRequest->id,
                    'type' => $type,
                    'transaction_id' => $existingTransaction->id,
                ]);
                return $existingTransaction;
            }

            $transaction = LogisticsTransaction::create([
                'procurement_request_id' => $procurementRequest->id,
                'product_id' => $procurementRequest->product_id,
                'branch_id' => $procurementRequest->branch_id,
                'source_branch_id' => $procurementRequest->branch_id,
                'destination_branch_id' => $procurementRequest->branch_id,
                'type' => $type,
                'status' => 'pending',
                'quantity' => $procurementRequest->quantity,
                'expected_quantity' => $procurementRequest->quantity,
                'unit' => 'unit',
                'reference_number' => "PR-{$procurementRequest->id}",
                'description' => $procurementRequest->product?->name,
                'created_by_user_id' => $userId,
                'initiated_at' => now(),
            ]);

            Log::info('Created logistics transaction', [
                'transaction_id' => $transaction->id,
                'procurement_id' => $procurementRequest->id,
                'type' => $type,
            ]);

            return $transaction;
        });
    }

    /**
     * Update logistics transaction status with validation
     */
    public function updateTransactionStatus($transactionId, $newStatus, $userId = null, $details = [])
    {
        return DB::transaction(function () use ($transactionId, $newStatus, $userId, $details) {
            $transaction = LogisticsTransaction::lockForUpdate()->find($transactionId);

            if (!$transaction) {
                throw new \Exception("Transaction not found: {$transactionId}");
            }

            if (!$transaction->canTransitionTo($newStatus)) {
                throw new \Exception(
                    "Cannot transition from {$transaction->status} to {$newStatus} for transaction {$transactionId}"
                );
            }

            // Update status and timestamp
            $transaction->status = $newStatus;
            $fieldName = $newStatus . '_at';
            $transaction->{$fieldName} = now();

            // Update quantity verified if provided
            if (isset($details['actual_quantity'])) {
                $transaction->actual_quantity = $details['actual_quantity'];
                $transaction->quantity_verified = $details['actual_quantity'];
            }

            // Update notes if provided
            if (isset($details['notes'])) {
                $transaction->notes = $details['notes'];
            }

            // Update variance reason if provided
            if (isset($details['variance_reason'])) {
                $transaction->variance_reason = $details['variance_reason'];
            }

            // Track who made the update
            if ($userId) {
                $transaction->updated_by_user_id = $userId;
                if ($newStatus === 'verified') {
                    $transaction->verified_by_user_id = $userId;
                }
            }

            $transaction->save();

            Log::info('Updated logistics transaction status', [
                'transaction_id' => $transactionId,
                'old_status' => $transaction->status,
                'new_status' => $newStatus,
                'user_id' => $userId,
            ]);

            return $transaction;
        });
    }

    /**
     * Mark a procurement request as in transit
     */
    public function markInTransit($procurementRequest, $userId = null)
    {
        return DB::transaction(function () use ($procurementRequest, $userId) {
            // Create or update logistics transaction
            $transaction = LogisticsTransaction::where('procurement_request_id', $procurementRequest->id)
                ->where('type', 'procurement')
                ->whereIn('status', ['pending', 'cancelled'])
                ->first();

            if (!$transaction) {
                $transaction = $this->createProcurementTransaction($procurementRequest, 'procurement', $userId);
            }

            // Transition to in_transit
            $this->updateTransactionStatus($transaction->id, 'in_transit', $userId);

            // Update procurement request status
            $procurementRequest->update([
                'status' => 'cash_in_transit',
            ]);

            return $transaction;
        });
    }

    /**
     * Mark a procurement request as at destination
     */
    public function markAtDestination($procurementRequest, $userId = null)
    {
        return DB::transaction(function () use ($procurementRequest, $userId) {
            $transaction = LogisticsTransaction::where('procurement_request_id', $procurementRequest->id)
                ->where('type', 'procurement')
                ->first();

            if (!$transaction) {
                throw new \Exception("No logistics transaction found for procurement {$procurementRequest->id}");
            }

            $this->updateTransactionStatus($transaction->id, 'at_destination', $userId);

            $procurementRequest->update([
                'status' => 'delivery_pending',
            ]);

            return $transaction;
        });
    }

    /**
     * Verify received quantities
     */
    public function verifyDelivery($procurementRequest, $actualQuantity, $userId = null, $notes = null)
    {
        return DB::transaction(function () use ($procurementRequest, $actualQuantity, $userId, $notes) {
            $transaction = LogisticsTransaction::where('procurement_request_id', $procurementRequest->id)
                ->where('type', 'procurement')
                ->first();

            if (!$transaction) {
                throw new \Exception("No logistics transaction found for procurement {$procurementRequest->id}");
            }

            // Check for variance
            $variance = $actualQuantity - $transaction->expected_quantity;
            $varianceDetails = [];

            if ($variance !== 0) {
                $varianceDetails['actual_quantity'] = $actualQuantity;
                $varianceDetails['variance_reason'] = $notes ?? "Variance: {$variance} units";
            }

            $this->updateTransactionStatus($transaction->id, 'verified', $userId, [
                'actual_quantity' => $actualQuantity,
                'notes' => $notes,
            ]);

            // Log variance warning if exists
            if ($variance !== 0) {
                Log::warning('Inventory variance detected', [
                    'procurement_id' => $procurementRequest->id,
                    'expected' => $transaction->expected_quantity,
                    'actual' => $actualQuantity,
                    'variance' => $variance,
                ]);
            }

            return $transaction;
        });
    }

    /**
     * Complete procurement and update inventory
     */
    public function completeProcurement($procurementRequest, $userId = null, $actualQuantity = null)
    {
        return DB::transaction(function () use ($procurementRequest, $userId, $actualQuantity) {
            $transaction = LogisticsTransaction::where('procurement_request_id', $procurementRequest->id)
                ->where('type', 'procurement')
                ->first();

            if (!$transaction) {
                throw new \Exception("No logistics transaction found for procurement {$procurementRequest->id}");
            }

            // Use verified quantity if available, otherwise use expected
            $quantityToAdd = $actualQuantity ?? $transaction->quantity_verified ?? $transaction->expected_quantity;

            // Update product stock
            $product = Product::find($procurementRequest->product_id);
            if ($product) {
                $product->increment('stock', $quantityToAdd);
                $product->update([
                    'has_been_ordered' => true,
                    'logistics_request_available' => false,
                ]);

                // Recompute real_stock
                try {
                    Product::recomputeRealStockForGroup($product->branch_id, $product->sku, $product->name);
                } catch (\Exception $e) {
                    Log::warning('Failed to recompute real_stock', [
                        'error' => $e->getMessage(),
                        'product_id' => $product->id,
                    ]);
                }
            }

            // Update logistics transaction
            $this->updateTransactionStatus($transaction->id, 'confirmed', $userId);
            $this->updateTransactionStatus($transaction->id, 'completed', $userId);

            // Update procurement request
            $procurementRequest->update([
                'status' => 'completed',
            ]);

            // Mark supplier order as fulfilled
            try {
                $supplierOrder = SupplierOrder::where('procurement_request_id', $procurementRequest->id)->first();
                if ($supplierOrder) {
                    $supplierOrder->update([
                        'status' => 'fulfilled',
                        'fulfilled_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to update supplier order', ['error' => $e->getMessage()]);
            }

            Log::info('Completed procurement with logistics tracking', [
                'procurement_id' => $procurementRequest->id,
                'transaction_id' => $transaction->id,
                'quantity_added' => $quantityToAdd,
                'user_id' => $userId,
            ]);

            return $transaction;
        });
    }

    /**
     * Create a stock transfer transaction between branches
     */
    public function createStockTransfer($sourceBranchId, $destinationBranchId, $productId, $quantity, $userId = null)
    {
        return DB::transaction(function () use ($sourceBranchId, $destinationBranchId, $productId, $quantity, $userId) {
            $product = Product::find($productId);
            if (!$product) {
                throw new \Exception("Product not found: {$productId}");
            }

            $transaction = LogisticsTransaction::create([
                'product_id' => $productId,
                'source_branch_id' => $sourceBranchId,
                'destination_branch_id' => $destinationBranchId,
                'branch_id' => $sourceBranchId,
                'type' => 'transfer',
                'status' => 'pending',
                'quantity' => $quantity,
                'expected_quantity' => $quantity,
                'unit' => 'unit',
                'reference_number' => "TRANSFER-" . now()->timestamp . "-" . uniqid(),
                'description' => "Stock transfer of {$product->name}",
                'created_by_user_id' => $userId,
                'initiated_at' => now(),
            ]);

            Log::info('Created stock transfer transaction', [
                'transaction_id' => $transaction->id,
                'from_branch' => $sourceBranchId,
                'to_branch' => $destinationBranchId,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);

            return $transaction;
        });
    }

    /**
     * Get logistics dashboard data for a branch or all branches
     */
    public function getDashboardData($branchId = null)
    {
        $query = LogisticsTransaction::with([
            'procurementRequest.product',
            'product',
            'sourceBranch',
            'destinationBranch',
            'createdByUser:id,full_name,username',
        ]);

        if ($branchId) {
            $query->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhere('source_branch_id', $branchId)
                  ->orWhere('destination_branch_id', $branchId);
            });
        }

        // Get pending operations
        $pending = $query->clone()->whereIn('status', ['pending', 'in_transit'])->count();

        // Get in-progress operations
        $inProgress = $query->clone()->where('status', 'at_destination')->count();

        // Get requiring verification
        $requiresVerification = $query->clone()->where('status', 'at_destination')->count();

        // Get with variance
        $withVariance = $query->clone()->whereNotNull('actual_quantity')
            ->whereRaw('actual_quantity != expected_quantity')
            ->count();

        // Get recent transactions
        $recent = $query->clone()
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return [
            'summary' => [
                'pending' => $pending,
                'in_progress' => $inProgress,
                'requires_verification' => $requiresVerification,
                'with_variance' => $withVariance,
            ],
            'recent_transactions' => $recent,
        ];
    }

    /**
     * Check for duplicate transactions and mark them
     */
    public function checkDuplicates($procurementId)
    {
        $transactions = LogisticsTransaction::where('procurement_request_id', $procurementId)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($transactions->count() > 1) {
            foreach ($transactions->skip(1) as $transaction) {
                $transaction->update([
                    'is_duplicate' => true,
                    'duplicate_of_transaction_id' => $transactions->first()->id,
                ]);
                Log::warning('Marked logistics transaction as duplicate', [
                    'transaction_id' => $transaction->id,
                    'duplicate_of' => $transactions->first()->id,
                    'procurement_id' => $procurementId,
                ]);
            }
        }
    }

    /**
     * Reconcile logistics records with actual inventory levels
     */
    public function reconcileInventory($branchId = null)
    {
        $query = LogisticsTransaction::where('status', 'completed');

        if ($branchId) {
            $query->where('destination_branch_id', $branchId);
        }

        /** @var LogisticsTransaction[] $transactions */
        $transactions = $query->with('product')->get();
        $discrepancies = [];

        foreach ($transactions as $transaction) {
            // Check if there's a variance between expected and actual quantity
            $variance = $transaction->getVariance();
            if ($variance !== null && $variance !== 0) {
                $discrepancies[] = [
                    'transaction_id' => $transaction->id,
                    'procurement_id' => $transaction->procurement_request_id,
                    'product_id' => $transaction->product_id,
                    'expected' => $transaction->expected_quantity,
                    'actual' => $transaction->actual_quantity,
                    'variance' => $variance,
                    'reason' => $transaction->variance_reason,
                ];
            }
        }

        return $discrepancies;
    }
}
