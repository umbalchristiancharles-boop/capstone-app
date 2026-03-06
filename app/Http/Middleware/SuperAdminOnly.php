<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminOnly
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Session-based check first
        $userId = Session::get('user_id');
        $userRole = Session::get('user_role');

        if ($userId && strtoupper($userRole) === 'SUPER_ADMIN') {
            return $next($request);
        }

        // Fallback to Auth user
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user && (isset($user->role) && strtoupper($user->role) === 'SUPER_ADMIN')) {
            return $next($request);
        }

        abort(403, 'Unauthorized. Super Admin access only.');
    }
}
