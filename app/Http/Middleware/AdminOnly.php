<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        if (Session::get('user_role') !== 'OWNER') {
            return redirect()->route('login')->with('error', 'Access denied.  Admin only.');
        }

        return $next($request);
    }
}
