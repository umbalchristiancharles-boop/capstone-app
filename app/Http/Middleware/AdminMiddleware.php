<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Support\Permission;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in
        if (!session('user_id')) {
            return redirect('/staff-landing')->with('error', 'Please login first.');
        }

        $user = User::find(session('user_id'));

        $allowed = Permission::allowed($user, ['ADMIN'], ['admin']);
        if (! $allowed) {
            return redirect('/staff-landing')->with('error', 'Unauthorized access. Admin only.');
        }

        return $next($request);
    }
}
