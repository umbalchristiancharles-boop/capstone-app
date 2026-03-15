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
        $authenticatedUser = null;

        // Check session first - using 'web' guard explicitly
        if (Session::has('user_id')) {
            // Try to log in the user from session
            $userId = Session::get('user_id');
            $user = \App\Models\User::find($userId);
            if ($user && $user->is_active) {
                Auth::guard('web')->login($user);
                $authenticatedUser = $user;
            }
        }

        // Check Laravel Auth (session-based) with explicit 'web' guard if not already authenticated
        if (!$authenticatedUser && Auth::guard('web')->check()) {
            $authenticatedUser = Auth::guard('web')->user();
            if (!$authenticatedUser || !$authenticatedUser->is_active) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return $this->unauthenticatedResponse($request);
            }
        }

        // Also check default Auth::check() for compatibility if not already authenticated
        if (!$authenticatedUser && Auth::check()) {
            $authenticatedUser = Auth::user();
            if (!$authenticatedUser || !$authenticatedUser->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return $this->unauthenticatedResponse($request);
            }
        }

        // Check Bearer token for Sanctum API authentication if not already authenticated
        if (!$authenticatedUser) {
            $token = $request->bearerToken();
            if ($token) {
                // Try to find the token in the database
                $accessToken = PersonalAccessToken::findToken($token);
                if ($accessToken && $accessToken->tokenable) {
                    // Check if user is active
                    if (!$accessToken->tokenable->is_active) {
                        return $this->unauthenticatedResponse($request);
                    }
                    // Set the user for Auth
                    Auth::login($accessToken->tokenable);
                    $authenticatedUser = $accessToken->tokenable;

                    // Also set session for compatibility
                    $request->session()->put('user_id', $accessToken->tokenable->id);
                    $request->session()->put('user_role', $accessToken->tokenable->role);
                }
            }
        }

        // If no authenticated user found, reject
        if (!$authenticatedUser) {
            return $this->unauthenticatedResponse($request);
        }

        // User is authenticated and active - proceed
        $response = $next($request);
        if (method_exists($response, 'header')) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }
        return $response;
    }

    /**
     * Handle unauthenticated responses consistently
     */
    private function unauthenticatedResponse(Request $request)
    {
        if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthenticated. Please login first.',
            ], 401);
        }

        return redirect('/staff-landing')->with('error', 'Please login first.');
    }
}
