<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Authenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (Session::has('user_id')) {
            $response = $next($request);
            if (method_exists($response, 'header')) {
                $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', '0');
            }
            return $response;
        }

        if (Auth::check()) {
            $response = $next($request);
            if (method_exists($response, 'header')) {
                $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', '0');
            }
            return $response;
        }

        if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthenticated. Please login first.',
            ], 401);
        }

        return redirect('/admin-login')->with('error', 'Please login first.');
    }
}
