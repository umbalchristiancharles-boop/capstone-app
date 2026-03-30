<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\TokenService;

/**
 * JwtToken Middleware - Validates Bearer tokens for cross-domain API requests
 */
class JwtToken
{
    public function handle(Request $request, Closure $next): Response
    {
        // Get token from Authorization header
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = substr($authHeader, 7); // Remove 'Bearer ' prefix
        $tokenService = new TokenService();
        
        $payload = $tokenService->validateAccessToken($token);

        if (!$payload) {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }

        // Store user info in request for later use
        $request->attributes->set('token_user_id', $payload['user_id']);
        $request->attributes->set('token_payload', $payload);

        return $next($request);
    }
}
