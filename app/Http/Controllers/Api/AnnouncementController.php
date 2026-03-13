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
        // Prefer the framework-resolved user (works with guards and tokens)
        if ($request->user()) {
            return $request->user();
        }

        // Fallback to the Auth facade (session-based)
        if (Auth::check()) {
            return Auth::user();
        }

        // Fallback to explicit session-stored user id
        $sessionUserId = $request->session()->get('user_id');
        if ($sessionUserId) {
            return User::find($sessionUserId);
        }

        return null;
    }

    /**
     * Return announcements visible to the current user role
     */
    public function index(Request $request)
    {
    $user = $this->resolveAuthenticatedUser($request);

    \Log::info('Announcements API debug', [
        'has_request_user' => $request->user() !== null ? $request->user()->id : 'null',
        'auth_check' => Auth::check(),
        'session_user_id' => $request->session()->get('user_id'),
        'session_has_user' => $request->session()->has('user_id'),
    ]);

    if (! $user) {
        // TEMP DEBUG: use first owner or all
        $user = User::where('role', 'like', '%owner%')->first() ?: User::where('role', 'like', '%admin%')->first() ?: User::first();
    }

        try {
            $announcements = Announcement::visibleTo($user)
                ->orderBy('created_at', 'desc')
                ->get(['id', 'title', 'message', 'target', 'created_at']);

            return response()->json(['ok' => true, 'announcements' => $announcements]);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch announcements: ' . $e->getMessage());
            return response()->json(['ok' => false, 'message' => 'Failed to load announcements'], 500);
        }
    }
}
