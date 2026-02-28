<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Sanctum\PersonalAccessToken;

class Authenticate
{
    public function handle(Request $request, Closure $next)
    {
        // Check session first
        if (Session::has('user_id')) {
            $response = $next($request);
            if (method_exists($response, 'header')) {
                $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', '0');
            }
            return $response;
        }

        // Check Laravel Auth (session-based)
        if (Auth::check()) {
            $response = $next($request);
            if (method_exists($response, 'header')) {
                $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', '0');
            }
            return $response;
        }

        // Check Bearer token for Sanctum API authentication
        $token = $request->bearerToken();
        if ($token) {
            // Try to find the token in the database
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken && $accessToken->tokenable) {
                // Set the user for Auth
                Auth::login($accessToken->tokenable);
                
                // Also set session for compatibility
                $request->session()->put('user_id', $accessToken->tokenable->id);
                $request->session()->put('user_role', $accessToken->tokenable->role);
                
                $response = $next($request);
                if (method_exists($response, 'header')) {
                    $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
                    $response->headers->set('Pragma', 'no-cache');
                    $response->headers->set('Expires', '0');
                }
                return $response;
            }
        }

        // Not authenticated
        if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthenticated. Please login first.',
            ], 401);
        }

        return redirect('/admin-login')->with('error', 'Please login first.');
    }
}
