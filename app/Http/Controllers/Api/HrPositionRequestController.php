<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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


        $created = PositionOpenRequest::create([
            'position_id' => (int) $request->input('position_id'),
            'requested_by_user_id' => (int) $user->id,
            'quantity' => (int) $request->input('quantity'),
            'notes' => $request->input('notes'),
            'status' => 'Pending',
        ]);


        return response()->json([
            'ok' => true,
            'message' => 'Position request submitted successfully.',
            'request' => $created,
        ]);
    }
}

