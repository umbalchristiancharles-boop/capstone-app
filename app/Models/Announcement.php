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
    public function scopeVisibleTo($query, string $userRole)
    {
        switch ($userRole) {
            case 'MANAGER':
                // Managers see announcements targeted to 'managers' or 'all'
                return $query->whereIn('target', ['managers', 'all']);
            case 'ADMIN':
                // Admins see all announcements
                return $query->where('target', 'all');
            default:
                // Staff see announcements targeted to 'staff' or 'all'
                return $query->whereIn('target', ['staff', 'all']);
        }
    }
}

