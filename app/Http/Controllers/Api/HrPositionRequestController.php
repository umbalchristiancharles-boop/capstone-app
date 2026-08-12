<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Position;
use App\Models\PositionOpenRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class HrPositionRequestController extends Controller
{


    // GET /api/hr/positions
    public function positions(Request $request)
    {
        $positions = Position::query()
            ->where('is_active', 1)
            ->orderBy('id')
            ->get(['id', 'name', 'department', 'description']);

        return response()->json([
            'ok' => true,
            'positions' => $positions,
        ]);
    }

    // GET /api/hr/positions/requests/pending - List pending requests (for main HR approval)
    public function pendingRequests(Request $request)
    {
        $query = PositionOpenRequest::with(['position', 'branch', 'requestedBy'])
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
                return [
                    'id' => $r->id,
                    'position_id' => $r->position_id,
                    'position_name' => optional($r->position)->name,
                    'department' => optional($r->position)->department,
                    'description' => optional($r->position)->description,
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

