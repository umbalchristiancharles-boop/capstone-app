<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    /**
     * Resolve authenticated user (works with session or Auth)
     */
    private function resolveAuthenticatedUser($request)
    {
        if (Auth::check()) return Auth::user();
        $sessionUserId = $request->session()->get('user_id');
        if ($sessionUserId) return User::find($sessionUserId);
        return null;
    }

    /**
     * Return announcements visible to the current user role
     */
    public function index(Request $request)
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (! $user) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated'], 401);
        }

        $role = strtoupper($user->role ?? '');

        try {
            $announcements = Announcement::visibleTo($role)
                ->orderBy('created_at', 'desc')
                ->get(['id', 'title', 'message', 'target', 'created_at']);

            return response()->json(['ok' => true, 'announcements' => $announcements]);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch announcements: ' . $e->getMessage());
            return response()->json(['ok' => false, 'message' => 'Failed to load announcements'], 500);
        }
    }
}
