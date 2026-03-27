<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Support\Permission;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (! Session::has('user_id')) {
            return redirect('/staff-landing')->with('error', 'Please login first.');
        }

        $user = User::find(Session::get('user_id'));
        $userRole = strtoupper(Session::get('user_role', ''));

        $allowed = Permission::allowed($user, ['ADMIN', 'OWNER'], ['admin']);
        if (! $allowed) {
            return redirect('/staff-landing')->with('error', 'Access denied. Admin only.');
        }

        return $next($request);
    }
}
