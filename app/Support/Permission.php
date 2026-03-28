<?php

namespace App\Support;

use App\Models\User;

class Permission
{
    /**
     * Check if user has one of the given roles (case-insensitive).
     */
    public static function hasRole(?User $user, array $roles): bool
    {
        if (! $user) return false;
        $role = strtoupper($user->role ?? '');
        foreach ($roles as $r) {
            if ($role === strtoupper($r)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Extract normalized permissions from the user record.
     */
    private static function extractPermissions(?User $user): array
    {
        $perms = $user?->permissions ?? [];
        
        // Handle JSON-encoded permissions
        if (is_string($perms)) {
            try {
                $perms = json_decode($perms, true) ?: [];
            } catch (\Throwable $e) {
                return ['modules' => [], 'functions' => []];
            }
        }
        
        if (!is_array($perms)) return ['modules' => [], 'functions' => []];
        
        $modules = array_map('strtolower', $perms['modules'] ?? []);
        $functions = array_map('strtolower', $perms['functions'] ?? []);
        return [
            'modules' => array_values(array_unique($modules)),
            'functions' => array_values(array_unique($functions)),
        ];
    }

    /**
     * Custom accounts (role CUSTOM) are allowed if they have any required module/function.
     */
    public static function customHas(array $requiredModules = [], array $requiredFunctions = [], ?User $user = null): bool
    {
        if (! $user || strtoupper($user->role ?? '') !== 'CUSTOM') return false;
        [$mods, $funcs] = [
            $requiredModules ? array_map('strtolower', $requiredModules) : [],
            $requiredFunctions ? array_map('strtolower', $requiredFunctions) : [],
        ];
        $perm = self::extractPermissions($user);
        if ($mods && count(array_intersect($perm['modules'], $mods)) > 0) return true;
        if ($funcs && count(array_intersect($perm['functions'], $funcs)) > 0) return true;
        if (!$mods && !$funcs) return true; // if nothing required, allow
        return false;
    }

    /**
     * Check if user is allowed via either roles or custom permissions.
     */
    public static function allowed(?User $user, array $roles = [], array $modules = [], array $functions = []): bool
    {
        if (!$user) return false;
        if ($roles && self::hasRole($user, $roles)) return true;
        if (strtoupper($user->role ?? '') === 'CUSTOM') {
            return self::customHas($modules, $functions, $user);
        }
        // If modules/functions required and user is not custom, allow only if a privileged role was provided and matched above.
        if ($modules || $functions) return false;
        // No extra requirements; fallback to existing role acceptance
        return false;
    }
}
