<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * These fields can be set when creating or updating an announcement.
     */
    protected $fillable = [
        'title',       // Announcement title
        'message',     // Announcement content/body
        'target',      // Target audience: 'all', 'staff', 'managers'
        'sender_id',  // The Super Admin user who created the announcement
    ];

    /**
     * The attributes that should be cast to native types.
     * Ensures proper type handling for dates and other fields.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user (Super Admin) who sent this announcement.
     * Defines a many-to-one relationship with the User model.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Scope to filter announcements by target audience.
     * Usage: Announcement::forTarget('staff')->get()
     */
    public function scopeForTarget($query, string $target)
    {
        return $query->where('target', $target);
    }

    /**
     * Scope to get all announcements for a specific user role.
     * This handles the logic of determining which announcements a user should see.
     */
    public function scopeVisibleTo($query, $userOrRole)
    {
        // Accept either a User instance or a role string
        if (is_string($userOrRole)) {
            $role = strtoupper(trim($userOrRole ?? ''));

            if (in_array($role, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER', 'ADMIN'])) {
                return $query; // show all
            }

            if (str_contains($role, 'MANAGER')) {
                return $query->whereIn('target', ['managers', 'all']);
            }

            return $query->whereIn('target', ['staff', 'all']);
        }

        // If a User object was provided, build more granular rules including branch/account targets
        $user = $userOrRole;
        if (! $user) return $query->where('id', 0); // no user, no announcements

        $role = strtoupper($user->role ?? '');

        // Owners, admins, superadmins see everything
        if (in_array($role, ['SUPER_ADMIN', 'SUPERADMIN', 'OWNER', 'ADMIN'])) {
            return $query;
        }

        // Custom accounts: if they have admin module, show all; if they have manager-like modules, treat as manager group
        $permissions = $user->permissions ?? [];
        $modules = is_array($permissions['modules'] ?? null) ? array_map('strtolower', $permissions['modules']) : [];
        $isCustomAdmin = $role === 'CUSTOM' && in_array('admin', $modules, true);
        $isCustomManager = $role === 'CUSTOM' && count(array_intersect($modules, ['finance', 'logistics', 'inventory', 'procurement', 'kitchen', 'cashier', 'hr'])) > 0;

        if ($isCustomAdmin) {
            return $query;
        }

        // Build conditional visibility: global + role-specific + account + branch-scoped
        return $query->where(function ($q) use ($user, $role, $isCustomManager) {
            // global announcements
            $q->where('target', 'all');

            // account-specific
            $q->orWhere('target', 'account:' . intval($user->id));

            // global role targets
            if (str_contains($role, 'MANAGER') || $isCustomManager) {
                $q->orWhere('target', 'managers');
            }
            // Staff and other regular roles
            if (! str_contains($role, 'MANAGER') && !$isCustomManager && ! in_array($role, ['ADMIN', 'OWNER', 'SUPER_ADMIN', 'SUPERADMIN'])) {
                $q->orWhere('target', 'staff');
            }

            // branch-scoped targets (if user has a branch)
            if ($user->branch_id) {
                $branchId = intval($user->branch_id);
                // branch-wide
                $q->orWhere('target', 'branch:' . $branchId . ':all');
                // branch-managers
                if (str_contains($role, 'MANAGER') || $isCustomManager) {
                    $q->orWhere('target', 'branch:' . $branchId . ':managers');
                }
                // branch-staff
                if (! str_contains($role, 'MANAGER') && !$isCustomManager && ! in_array($role, ['ADMIN', 'OWNER', 'SUPER_ADMIN', 'SUPERADMIN'])) {
                    $q->orWhere('target', 'branch:' . $branchId . ':staff');
                }
            }
        });
    }
}

