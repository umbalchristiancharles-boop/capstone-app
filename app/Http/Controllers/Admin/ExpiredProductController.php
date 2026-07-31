<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpiredProductReport;
use App\Models\Product;
use App\Models\InventoryLot;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExpiredProductController extends Controller
{
    /**
     * Get all expired product reports across all branches
     */
    public function index(Request $request)
    {
        try {
            $reports = ExpiredProductReport::with(['product', 'reporter', 'branch'])
                ->orderBy('created_at', 'desc')
                ->get();

            $payload = $reports->map(function ($report) {
                // Get all inventory lots for this product to show expiry history
                $inventoryLots = InventoryLot::where('product_id', $report->product_id)
                    ->where('branch_id', $report->branch_id)
                    ->orderBy('expires_at', 'desc')
                    ->get(['id', 'quantity', 'expires_at', 'created_at']);

                return [
                    'id' => $report->id,
                    'product_id' => $report->product_id,
                    'product_name' => $report->product?->name,
                    'product_sku' => $report->product?->sku,
                    'product_stock' => $report->product?->stock ?? 0,
                    'product_price' => $report->product?->price ?? 0,
                    'expires_at' => $report->product?->expires_at,
                    'branch_id' => $report->branch_id,
                    'branch_name' => $report->branch?->name ?? 'N/A',
                    'quantity' => $report->quantity,
                    'notes' => $report->notes,
                    'image_path' => $report->image_path,
                    'status' => $report->status,
                    'reported_by' => $report->reporter?->full_name ?? $report->reporter?->username ?? 'Unknown',
                    'created_at' => $report->created_at,
                    'updated_at' => $report->updated_at,
                    'inventory_lots' => $inventoryLots,
                ];
            });

            return response()->json(['ok' => true, 'data' => $payload]);
        } catch (\Exception $e) {
            Log::error('Failed to load expired product reports', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch expired product reports'], 500);
        }
    }

   
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $validated = $request->validate([
            'status' => 'required|in:reviewed,resolved',
            'notes' => 'nullable|string|max:2000',
        ]);

        try {
            $report = ExpiredProductReport::findOrFail($id);
            $report->status = $validated['status'];
            
            if (!empty($validated['notes'])) {
                $report->notes = $report->notes . "\n\n[Admin Review]: " . $validated['notes'];
            }
            
            $report->reviewed_by = $user->id;
            $report->reviewed_at = now();
            $report->save();

            Log::info('Expired product report status updated', [
                'report_id' => $report->id,
                'new_status' => $validated['status'],
                'reviewed_by' => $user->id,
            ]);

            return response()->json([
                'ok' => true,
                'message' => 'Report status updated successfully',
                'data' => $report
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update expired product report status', [
                'error' => $e->getMessage(),
                'report_id' => $id,
            ]);
            return response()->json(['error' => 'Failed to update report status'], 500);
        }
    }

       public function statistics(Request $request)
    {
        try {
            $pending = ExpiredProductReport::where('status', 'pending')->count();
            $reviewed = ExpiredProductReport::where('status', 'reviewed')->count();
            $resolved = ExpiredProductReport::where('status', 'resolved')->count();
            $total = ExpiredProductReport::count();

            return response()->json([
                'ok' => true,
                'data' => [
                    'pending' => $pending,
                    'reviewed' => $reviewed,
                    'resolved' => $resolved,
                    'total' => $total,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load expired product statistics', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch statistics'], 500);
        }
    }
}