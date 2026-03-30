<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConfigController extends Controller
{
    /**
     * Return the application's default password setting.
     * Only OWNER or ADMIN may retrieve this value.
     */
    public function defaultPassword(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        if (! in_array($user->role, ['OWNER', 'ADMIN'])) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        try {
            $pw = config('chikintayo.default_password');
            return response()->json(['success' => true, 'default_password' => $pw]);
        } catch (\Exception $e) {
            Log::warning('Could not fetch default password: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch config'], 500);
        }
    }

    /**
     * Return panel descriptions from shared configuration.
     */
    public function panelDescriptions(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        try {
            $descriptions = config('panel_descriptions', []);
            return response()->json([
                'success' => true,
                'descriptions' => is_array($descriptions) ? $descriptions : [],
            ]);
        } catch (\Exception $e) {
            Log::warning('Could not fetch panel descriptions: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch panel descriptions'], 500);
        }
    }
}
