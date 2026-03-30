<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * TokenService - Handles JWT token creation, validation, and refresh
 * Works across multiple domains without session cookies
 */
class TokenService
{
    private const ACCESS_TOKEN_EXPIRY_MINUTES = 15;
    private const REFRESH_TOKEN_EXPIRY_DAYS = 7;
    private const TOKEN_PREFIX = 'token_';

    /**
     * Issue access and refresh tokens for a user
     *
     * @param User $user
     * @return array
     */
    public function issueTokens(User $user)
    {
        $accessToken = $this->generateAccessToken($user);
        $refreshToken = $this->generateRefreshToken($user);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => self::ACCESS_TOKEN_EXPIRY_MINUTES * 60,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'department' => $user->department ?? null,
                'branch_id' => $user->branch_id,
                'full_name' => $user->full_name,
            ],
        ];
    }

    /**
     * Generate a short-lived access token
     *
     * @param User $user
     * @return string
     */
    private function generateAccessToken(User $user): string
    {
        $token = Str::random(64);
        
        // Store token in cache with expiry (prevents DB queries on every request)
        cache()->put(
            'access_token:' . $token,
            [
                'user_id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
                'brand' => 'staff_or_customer', // Can be set per domain
            ],
            now()->addMinutes(self::ACCESS_TOKEN_EXPIRY_MINUTES)
        );

        return $token;
    }

    /**
     * Generate a long-lived refresh token (stored in DB)
     *
     * @param User $user
     * @return string
     */
    private function generateRefreshToken(User $user): string
    {
        // Use Sanctum's personal access token feature
        $sanctumToken = $user->createToken('refresh-' . Str::random(20), ['*']);
        
        return $sanctumToken->plainTextToken;
    }

    /**
     * Validate and decode an access token
     *
     * @param string $token
     * @return array|null
     */
    public function validateAccessToken(string $token): ?array
    {
        $cached = cache()->get('access_token:' . $token);
        
        if (!$cached) {
            return null;
        }

        return $cached;
    }

    /**
     * Refresh an access token using a refresh token
     *
     * @param string $refreshToken
     * @return array|null
     */
    public function refreshAccessToken(string $refreshToken): ?array
    {
        try {
            // Find token in Sanctum (PersonalAccessToken)
            $token = \Laravel\Sanctum\PersonalAccessToken::findToken($refreshToken);
            
            if (!$token) {
                return null;
            }

            $user = $token->tokenable;

            // Check if user is still active
            if (!$user || !$user->is_active) {
                $token->delete();
                return null;
            }

            // Issue new tokens
            return $this->issueTokens($user);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Revoke a refresh token
     *
     * @param string $refreshToken
     * @return bool
     */
    public function revokeRefreshToken(string $refreshToken): bool
    {
        try {
            $token = \Laravel\Sanctum\PersonalAccessToken::findToken($refreshToken);
            
            if ($token) {
                $token->delete();
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Revoke all tokens for a user (logout)
     *
     * @param User $user
     * @return bool
     */
    public function revokeAllTokens(User $user): bool
    {
        try {
            $user->tokens()->delete();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get user from access token
     *
     * @param string $token
     * @return User|null
     */
    public function getUserFromAccessToken(string $token): ?User
    {
        $payload = $this->validateAccessToken($token);
        
        if (!$payload) {
            return null;
        }

        return User::find($payload['user_id']);
    }
}
