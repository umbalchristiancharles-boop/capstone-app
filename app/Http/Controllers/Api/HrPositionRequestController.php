<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Position;
use App\Models\PositionOpenRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HrPositionRequestController extends Controller
{
    private const CUSTOM_ACCOUNT_MODULES = [
        'budget-request-approvals', 'current-price-markup', 'receipt-approval',
        'financial-overview', 'recent-transactions', 'manual-procurement',
        'procurement-requests', 'add-supplier', 'supplier-products',
        'stock-confirmation-history', 'product-barcodes', 'pending-stock',
        'request-new-product', 'supplier-product-confirmations', 'inventory-monitor',
        'product-disposal', 'landing-page-product-images', 'staff-activity',
        'automatic-clock-out', 'staff-management',
    ];

    private function isMainBranchHr($user): bool
    {
        if (! $user || ! in_array(strtoupper((string) $user->role), ['MANAGER', 'MANAGER_HR', 'HR'], true)
            || strtoupper((string) $user->department) !== 'HR') {
            return false;
        }

        return Branch::query()
            ->whereKey($user->branch_id)
            ->where('is_main_branch', true)
            ->exists();
    }

    // GET /api/hr/positions/branches/awaiting-open-positions
    public function branchesAwaitingOpenPositions(Request $request)
    {
        $user = Auth::user();
        if (! $this->isMainBranchHr($user)) {
            return response()->json(['ok' => false, 'message' => 'Only Main Branch HR can manage these openings.'], 403);
        }

        $branches = Branch::query()
            ->where('is_main_branch', false)
            ->where('is_active', true)
            ->where('approval_status', 'approved')
            ->whereNotNull('approved_at')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('position_open_requests')
                    ->whereColumn('position_open_requests.branch_id', 'branches.id')
                    ->whereIn('position_open_requests.status', ['Pending', 'Approved']);
            })
            ->orderBy('approved_at')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'address', 'approved_at']);

        return response()->json(['ok' => true, 'branches' => $branches]);
    }

    // POST /api/hr/positions/branches/{branch}/open-positions
    public function createBranchOpenPositions(Request $request, int $branchId)
    {
        $user = Auth::user();
        if (! $this->isMainBranchHr($user)) {
            return response()->json(['ok' => false, 'message' => 'Only Main Branch HR can manage these openings.'], 403);
        }

        $validated = $request->validate([
            'positions' => ['required', 'array', 'min:1'],
            'positions.*.position_id' => ['required', 'integer', 'exists:positions,id'],
            'positions.*.quantity' => ['required', 'integer', 'min:1', 'max:500'],
            'positions.*.account_type' => ['nullable', Rule::in(['standard', 'custom'])],
            'positions.*.account_config' => ['nullable', 'array'],
            'positions.*.account_config.modules' => ['nullable', 'array'],
            'positions.*.account_config.modules.*' => ['string', Rule::in(self::CUSTOM_ACCOUNT_MODULES)],
        ]);

        $branch = Branch::query()
            ->whereKey($branchId)
            ->where('is_main_branch', false)
            ->where('is_active', true)
            ->where('approval_status', 'approved')
            ->first();

        if (! $branch) {
            return response()->json(['ok' => false, 'message' => 'Approved branch not found.'], 404);
        }

        $positions = Position::query()
            ->whereIn('id', collect($validated['positions'])->pluck('position_id'))
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        if ($positions->count() !== collect($validated['positions'])->pluck('position_id')->unique()->count()) {
            return response()->json(['ok' => false, 'message' => 'One or more selected positions are unavailable.'], 422);
        }

        $positionIdCounts = collect($validated['positions'])
            ->countBy(fn (array $item) => (int) $item['position_id']);

        foreach ($validated['positions'] as $index => $item) {
            $position = $positions->get((int) $item['position_id']);
            $isCustom = strtoupper((string) $position->department) === 'CUSTOM'
                || strtolower((string) $position->name) === 'custom account';
            $isConfiguredCustom = ($item['account_type'] ?? 'standard') === 'custom'
                && ! empty($item['account_config']['modules']);

            if (! $isCustom && $positionIdCounts->get((int) $item['position_id']) > 1) {
                return response()->json([
                    'ok' => false,
                    'message' => 'A standard position can only be selected once.',
                    'errors' => ["positions.{$index}.position_id" => ['Duplicate standard position.']],
                ], 422);
            }

            if ($isCustom !== $isConfiguredCustom) {
                return response()->json([
                    'ok' => false,
                    'message' => $isCustom
                        ? 'Select at least one access module for the custom account.'
                        : 'Custom account settings are only valid for the Custom Account position.',
                    'errors' => ["positions.{$index}.account_config.modules" => ['Custom account settings are invalid.']],
                ], 422);
            }
        }

        $requests = DB::transaction(function () use ($branch, $validated, $positions, $user) {
            $hasExistingOpenings = PositionOpenRequest::query()
                ->where('branch_id', $branch->id)
                ->whereIn('status', ['Pending', 'Approved'])
                ->lockForUpdate()
                ->exists();

            if ($hasExistingOpenings) {
                return null;
            }

            return collect($validated['positions'])->map(function (array $item) use ($branch, $positions, $user) {
                return PositionOpenRequest::create([
                    'position_id' => (int) $item['position_id'],
                    'branch_id' => $branch->id,
                    'requested_by_user_id' => $user->id,
                    'quantity' => (int) $item['quantity'],
                    'notes' => 'Open positions created by Main Branch HR.',
                    'account_type' => $item['account_type'] ?? 'standard',
                    'account_config' => ($item['account_type'] ?? 'standard') === 'custom'
                        ? $item['account_config']
                        : null,
                    'status' => 'Approved',
                    'approved_by_user_id' => $user->id,
                    'approved_at' => now(),
                ]);
            });
        });

        if ($requests === null) {
            return response()->json(['ok' => false, 'message' => 'This branch already has open-position requests.'], 409);
        }

        return response()->json([
            'ok' => true,
            'message' => "Open positions published for {$branch->name}.",
            'requests' => $requests,
        ], 201);
    }

    // GET /api/hr/positions
    public function positions(Request $request)
    {
        $positions = Position::query()
            ->where('is_active', 1)
            ->orderBy('id')
            ->get(['id', 'name', 'department', 'description']);

        return response()->json([
            'ok' => true,
            'positions' => $positions->map(function (Position $position) {
                return [
                    'id' => $position->id,
                    'name' => $position->name,
                    'department' => $position->department,
                    'description' => $position->description,
                    'is_custom' => strtoupper((string) $position->department) === 'CUSTOM'
                        || strtolower((string) $position->name) === 'custom account',
                ];
            }),
        ]);
    }

    // GET /api/hr/positions/requests/pending - List pending requests (for main HR approval)
    public function pendingRequests(Request $request)
    {
        $query = PositionOpenRequest::with(['position', 'branch', 'requestedBy'])
            ->where(function ($query) {
                $query->whereNull('branch_id')
                    ->orWhereHas('branch', function ($branchQuery) {
                        $branchQuery->where(function ($approvalQuery) {
                            $approvalQuery->whereNull('approval_status')
                                ->orWhere('approval_status', 'approved');
                        });
                    });
            })
            ->orderBy('created_at', 'desc');

        // Filter by branch if provided
        $branchId = $request->query('branch_id');
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $requests = $query->get();

        return response()->json([
            'ok' => true,
            'requests' => $requests,
        ]);
    }

    // POST /api/hr/positions/requests
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'position_id' => ['required', 'integer', 'exists:positions,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'account_type' => ['nullable', Rule::in(['standard', 'custom'])],
            'account_config' => ['nullable', 'array'],
            'account_config.modules' => ['nullable', 'array'],
            'account_config.modules.*' => ['string', 'max:100'],
            'account_config.functions' => ['nullable', 'array'],
            'account_config.functions.*' => ['string', 'max:100'],
        ]);

        // Authorization is handled by route middleware auth + the HR panel access.
        // Do not enforce strict role parsing here because the codebase role strings vary.


        // Get branch_id from user's branch association
        $branchId = null;
        if ($user->branch_id) {
            $branchId = (int) $user->branch_id;
        } elseif ($request->has('branch_id')) {
            $branchId = (int) $request->input('branch_id');
        }

        $data = [
            'position_id' => (int) $request->input('position_id'),
            'requested_by_user_id' => (int) $user->id,
            'quantity' => (int) $request->input('quantity'),
            'notes' => $request->input('notes'),
            'account_type' => $request->input('account_type', 'standard'),
            'account_config' => $request->input('account_type') === 'custom'
                ? $request->input('account_config', [])
                : null,
            'status' => 'Pending',
        ];

        // Only set branch_id if we have a valid value
        if ($branchId) {
            $data['branch_id'] = $branchId;
        }

        $created = PositionOpenRequest::create($data);


        return response()->json([
            'ok' => true,
            'message' => 'Position request submitted. Waiting for main HR approval.',
            'request' => $created,
        ]);
    }

    // POST /api/hr/positions/requests/{id}/approve
    public function approve(Request $request, $id)
    {
        $user = Auth::user();

        $positionRequest = PositionOpenRequest::findOrFail($id);

        if ($positionRequest->status !== 'Pending') {
            return response()->json([
                'ok' => false,
                'message' => 'Request is not pending approval.',
            ], 400);
        }

        $positionRequest->update([
            'status' => 'Approved',
            'approved_by_user_id' => (int) $user->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Position request approved.',
            'request' => $positionRequest->fresh(['position', 'branch', 'requestedBy']),
        ]);
    }

    // GET /api/public/positions/approved - List approved open positions (customer landing)
    public function approvedOpenPositions(Request $request)
    {
        $query = PositionOpenRequest::query()
            ->where('status', 'Approved')
            ->where('quantity', '>', 0)  // ✅ Hide positions with no remaining slots
            ->where(function ($query) {
                $query->whereNull('branch_id')
                    ->orWhereHas('branch', function ($branchQuery) {
                        $branchQuery->where(function ($approvalQuery) {
                            $approvalQuery->whereNull('approval_status')
                                ->orWhere('approval_status', 'approved');
                        });
                    });
            })
            ->with(['position', 'branch'])
            ->orderByDesc('approved_at');

        // Optional: filter by branch_id if frontend ever needs it
        $branchId = $request->query('branch_id');
        if ($branchId) {
            $query->where('branch_id', (int) $branchId);
        }

        $requests = $query->get();

        return response()->json([
            'ok' => true,
            'approved_positions' => $requests->map(function ($r) {
                $customModules = $r->account_type === 'custom'
                    ? ($r->account_config['modules'] ?? [])
                    : [];
                $moduleLabels = collect($customModules)->map(function ($module) {
                    return ucfirst(str_replace(['_', '-'], ' ', $module));
                })->values()->all();
                $isCustom = $r->account_type === 'custom';

                return [
                    'id' => $r->id,
                    'position_id' => $r->position_id,
                    // Keep the submitted job title within the applications table limit.
                    // The selected custom modules remain available in the description.
                    'position_name' => $isCustom
                        ? 'Custom Account'
                        : optional($r->position)->name,
                    'department' => $isCustom ? null : optional($r->position)->department,
                    'description' => $isCustom
                        ? 'Role access: ' . implode(', ', $moduleLabels)
                        : optional($r->position)->description,
                    'branch_id' => $r->branch_id,
                    'branch_name' => optional($r->branch)->name,
                    'quantity' => $r->quantity,
                    'approved_at' => $r->approved_at,
                ];
            }),
        ]);
    }

    // POST /api/hr/positions/requests/{id}/reject
    public function reject(Request $request, $id)
    {
        $user = Auth::user();

        $request->validate([

            'reason' => ['nullable', 'string', 'max:5000'],
        ]);

        $positionRequest = PositionOpenRequest::findOrFail($id);

        if ($positionRequest->status !== 'Pending') {
            return response()->json([
                'ok' => false,
                'message' => 'Request is not pending approval.',
            ], 400);
        }

        $positionRequest->update([
            'status' => 'Rejected',
            'approved_by_user_id' => (int) $user->id,
            'approved_at' => now(),
            'rejection_reason' => $request->input('reason'),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Position request rejected.',
            'request' => $positionRequest->fresh(['position', 'branch', 'requestedBy']),
        ]);
    }
}
