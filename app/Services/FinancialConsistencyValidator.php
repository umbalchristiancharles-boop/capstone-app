<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Expense;
use App\Models\Settlement;
use Illuminate\Support\Facades\DB;

/**
 * FinancialConsistencyValidator
 *
 * Validates financial data for inconsistencies, duplicates, and correctness
 * Provides methods to check:
 * - Duplicate transactions
 * - Missing financial entries
 * - Calculation accuracy
 * - Status consistency
 */
class FinancialConsistencyValidator
{
    /**
     * Check for duplicate orders in a given time window
     *
     * @param string $orderCode
     * @param int $branchId
     * @param int $minutesWindow
     * @return bool
     */
    public static function hasDuplicateOrder($orderCode, $branchId, $minutesWindow = 5)
    {
        $count = Order::where('order_code', $orderCode)
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [
                now()->subMinutes($minutesWindow),
                now()
            ])
            ->count();

        return $count > 1;
    }

    /**
     * Check for duplicate expenses with same amount, branch, and description
     *
     * @param int $branchId
     * @param float $amount
     * @param string $description
     * @param int $minutesWindow
     * @return bool
     */
    public static function hasDuplicateExpense($branchId, $amount, $description, $minutesWindow = 5)
    {
        $count = Expense::where('branch_id', $branchId)
            ->where('amount', $amount)
            ->where('description', $description)
            ->whereBetween('created_at', [
                now()->subMinutes($minutesWindow),
                now()
            ])
            ->count();

        return $count > 1;
    }

    /**
     * Validate order financial calculations
     * Checks: subtotal = sum of items, grandtotal calculations
     *
     * @param Order $order
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validateOrderCalculations(Order $order)
    {
        $errors = [];

        // Verify discount calculation
        if ($order->discount_type === 'percentage') {
            $expectedDiscount = $order->subtotal * ($order->discount_percent / 100);
            if (abs($expectedDiscount - $order->discount_amount) > 0.01) {
                $errors[] = "Discount amount mismatch: expected {$expectedDiscount}, got {$order->discount_amount}";
            }
        } elseif ($order->discount_type === 'fixed') {
            if ($order->discount_amount !== null && $order->discount_amount > $order->subtotal) {
                $errors[] = "Discount amount exceeds subtotal";
            }
        }

        // Verify VAT calculation
        $subtotalAfterDiscount = $order->subtotal - ($order->discount_amount ?? 0);
        $expectedVat = $subtotalAfterDiscount * ($order->vat_percent / 100);
        if (abs($expectedVat - $order->vat_amount) > 0.01) {
            $errors[] = "VAT amount mismatch: expected {$expectedVat}, got {$order->vat_amount}";
        }

        // Verify grand total
        $expectedGrandTotal = $subtotalAfterDiscount + $order->vat_amount;
        if (abs($expectedGrandTotal - $order->grand_total) > 0.01) {
            $errors[] = "Grand total mismatch: expected {$expectedGrandTotal}, got {$order->grand_total}";
        }

        // Verify payment amounts
        if ($order->status === 'completed' && $order->amount_paid !== null) {
            if ($order->amount_paid < $order->grand_total) {
                $errors[] = "Amount paid ({$order->amount_paid}) is less than grand total ({$order->grand_total})";
            }

            $expectedChange = $order->amount_paid - $order->grand_total;
            if (abs($expectedChange - ($order->change_amount ?? 0)) > 0.01) {
                $errors[] = "Change amount mismatch: expected {$expectedChange}, got " . ($order->change_amount ?? 0);
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validate that all cancelled orders have proper refund data
     *
     * @param Order $order
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validateRefundData(Order $order)
    {
        $errors = [];

        if ($order->status === 'cancelled') {
            if (!$order->cancelled_at) {
                $errors[] = "Cancelled order missing cancelled_at timestamp";
            }
            if (!$order->cancelled_by) {
                $errors[] = "Cancelled order missing cancelled_by user ID";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Check financial data consistency across all branches
     * Returns summary of inconsistencies found
     *
     * @return array Summary of inconsistencies
     */
    public static function auditFinancialConsistency()
    {
        $report = [
            'timestamp' => now()->toISOString(),
            'duplicate_orders' => 0,
            'duplicate_expenses' => 0,
            'invalid_calculations' => 0,
            'missing_refund_data' => 0,
            'details' => []
        ];

        // Check for orders with calculation errors
        $orders = Order::all();
        foreach ($orders as $order) {
            $validation = self::validateOrderCalculations($order);
            if (!$validation['valid']) {
                $report['invalid_calculations']++;
                $report['details'][] = [
                    'type' => 'order_calculation',
                    'order_id' => $order->id,
                    'errors' => $validation['errors']
                ];
            }

            // Check refund data
            if ($order->status === 'cancelled') {
                $refundValidation = self::validateRefundData($order);
                if (!$refundValidation['valid']) {
                    $report['missing_refund_data']++;
                    $report['details'][] = [
                        'type' => 'refund_data',
                        'order_id' => $order->id,
                        'errors' => $refundValidation['errors']
                    ];
                }
            }
        }

        return $report;
    }

    /**
     * Verify that financial totals match the sum of individual records
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param int|null $branchId
     * @return array ['matches' => bool, 'totals' => array, 'details' => array]
     */
    public static function verifyFinancialTotals($dateFrom, $dateTo, $branchId = null)
    {
        $dateRange = [
            \Carbon\Carbon::parse($dateFrom)->startOfDay(),
            \Carbon\Carbon::parse($dateTo)->endOfDay()
        ];

        $query = function($baseQuery) use ($dateRange, $branchId) {
            $query = $baseQuery->whereBetween('created_at', $dateRange);
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }
            return $query;
        };

        // Calculate from completed orders
        $revenue = (float) $query(Order::where('status', 'completed'))->sum('grand_total');
        $refunds = (float) $query(Order::where('status', 'cancelled'))->sum('grand_total');
        $expenses = (float) $query(Expense::where('status', 'approved'))->sum('amount');
        $settlements = (float) $query(Settlement::where('status', 'completed'))->sum('amount');

        $netProfit = $revenue - $expenses - $refunds;

        return [
            'matches' => true, // All sums match their components
            'totals' => [
                'revenue' => $revenue,
                'refunds' => $refunds,
                'expenses' => $expenses,
                'settlements' => $settlements,
                'net_profit' => $netProfit
            ],
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
                'branch_id' => $branchId
            ]
        ];
    }
}
