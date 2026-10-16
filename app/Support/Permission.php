<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Session;

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
     * Check whether a user has any required module or function permission.
     */
    public static function hasPermissions(
        ?User $user,
        array $requiredModules = [],
        array $requiredFunctions = []
    ): bool
    {
        if (! $user) return false;

        [$mods, $funcs] = [
            $requiredModules ? array_map('strtolower', $requiredModules) : [],
            $requiredFunctions ? array_map('strtolower', $requiredFunctions) : [],
        ];

        $perm = self::extractPermissions($user);
        if ($mods) {
            $moduleFeatureAliases = [
                'finance' => [
                    'budget-request-approvals',
                    'current-price-markup',
                    'receipt-approval',
                    'financial-overview',
                    'recent-transactions',
                ],
                'procurement' => [
                    'manual-procurement',
                    'procurement-requests',
                    'add-supplier',
                    'supplier-products',
                ],
                'logistics' => [
                    'stock-confirmation-history',
                    'product-barcodes',
                    'pending-stock',
                    'request-new-product',
                    'supplier-product-confirmations',
                ],
                'inventory' => [
                    'inventory-monitor',
                    'product-disposal',
                ],
                'hr' => [
                    'staff-activity',
                    'automatic-clock-out',
                    'staff-management',
                ],
            ];

            foreach ($mods as $module) {
                if (in_array($module, $perm['modules'], true)) return true;
                if (count(array_intersect($moduleFeatureAliases[$module] ?? [], $perm['modules'])) > 0) {
                    return true;
                }
            }
        }
        if ($funcs && count(array_intersect($perm['functions'], $funcs)) > 0) return true;
        if (!$mods && !$funcs) return true;
        return false;
    }

    /**
     * Backward-compatible alias for custom-account permission checks.
     */
    public static function customHas(
        array $requiredModules = [],
        array $requiredFunctions = [],
        ?User $user = null
    ): bool {
        return self::hasPermissions($user, $requiredModules, $requiredFunctions);
    }

    /**
     * Check if user is allowed via roles or assigned permissions.
     */
    public static function allowed(?User $user, array $roles = [], array $modules = [], array $functions = []): bool
    {
        if (!$user) return false;
        if (in_array(strtoupper($user->role ?? ''), ['SUPER_ADMIN', 'SUPERADMIN'], true)
            || ((int) Session::get('user_id') === (int) $user->id
                && in_array(strtoupper((string) Session::get('user_role')), ['SUPER_ADMIN', 'SUPERADMIN'], true))) {
            return true;
        }
        if ($roles && self::hasRole($user, $roles)) return true;
        if (!$modules && !$functions) return false;
        return self::hasPermissions($user, $modules, $functions);
    }
}
