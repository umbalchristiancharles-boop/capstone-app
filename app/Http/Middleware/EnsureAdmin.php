<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check session-based authentication (primary for this SPA)
        $userId = session('user_id');
        $userRole = session('user_role');

        if ($userId && in_array(strtoupper((string) $userRole), ['ADMIN', 'SUPER_ADMIN', 'SUPERADMIN'], true)) {
            return $next($request);
        }

        // Fallback: Check Laravel's Auth facade
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user && (isset($user->role) && in_array(strtoupper($user->role), ['ADMIN', 'SUPER_ADMIN', 'SUPERADMIN'], true)
            || isset($user->is_admin) && $user->is_admin)) {
            return $next($request);
        }

        // Not admin - return 403
        abort(403, 'Unauthorized. Admin access only.');
    }
}
