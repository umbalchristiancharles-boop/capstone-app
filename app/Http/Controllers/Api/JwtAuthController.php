<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * JwtAuthController - Handles JWT token-based authentication for cross-domain deployment
 * Issues access tokens (short-lived) and refresh tokens (long-lived)
 */
class JwtAuthController extends Controller
{
    private TokenService $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * POST /api/jwt/login
     * Issue tokens for username/password (works across domains)
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->input('username'))
            ->where('is_active', 1)
            ->first();

        // Invalid credentials or inactive user
        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'error' => 'Invalid username or password',
            ], 401);
        }

        // Check if user's branch is active (deactivated branches cannot login)
        if ($user->branch_id) {
            $branch = \App\Models\Branch::find($user->branch_id);
            if ($branch && !$branch->is_active) {
                return response()->json([
                    'error' => 'Your branch has been deactivated. Please contact support.',
                ], 403);
            }
        }

        $tokens = $this->tokenService->issueTokens($user);

        return response()->json($tokens);
    }

    /**
     * POST /api/jwt/refresh
     * Issue new access token using refresh token
     */
    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $tokens = $this->tokenService->refreshAccessToken(
            $request->input('refresh_token')
        );

        if (!$tokens) {
            return response()->json([
                'error' => 'Invalid or expired refresh token',
            ], 401);
        }

        return response()->json($tokens);
    }

    /**
     * POST /api/jwt/logout
     * Revoke refresh token (invalidate future refresh attempts)
     * Requires valid access token
     */
    public function logout(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $this->tokenService->revokeRefreshToken(
            $request->input('refresh_token')
        );

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * POST /api/jwt/logout-all
     * Revoke ALL tokens for current user
     * Requires valid access token
     */
    public function logoutAll(Request $request)
    {
        $userId = $request->attributes->get('token_user_id');
        
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $this->tokenService->revokeAllTokens($user);

        return response()->json(['message' => 'All sessions logged out']);
    }

    /**
     * GET /api/jwt/me
     * Get current user info using access token
     */
    public function me(Request $request)
    {
        $userId = $request->attributes->get('token_user_id');

        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'full_name' => $user->full_name,
            'role' => $user->role,
            'department' => $user->department,
            'branch_id' => $user->branch_id,
            'avatar_url' => $user->avatar_url,
        ]);
    }
}
