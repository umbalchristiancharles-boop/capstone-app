<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class OwnerOnly
{
    public function handle(Request $request, Closure $next)
    {
        // First check session-based authentication (primary for this SPA)
        $userId = Session::get('user_id');
        $userRole = Session::get('user_role');

        if ($userId && strtoupper($userRole) === 'OWNER') {
            return $next($request);
        }

        // Fallback: Check Laravel's Auth facade
        $user = Auth::user();
        if ($user && $user->role === 'OWNER') {
            return $next($request);
        }

        // Not owner - return 403
        abort(403, 'Unauthorized. Owner access only.');
    }
}
