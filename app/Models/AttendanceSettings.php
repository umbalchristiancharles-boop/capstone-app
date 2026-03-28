<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Branch;

class AttendanceSettings extends Model
{
    protected $table = 'attendance_settings';

    protected $fillable = [
        'branch_id',
        'early_clockout_override',
    ];

    protected $casts = [
        'early_clockout_override' => 'boolean',
    ];

    /**
     * Get the branch that owns the attendance settings.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Get or create settings for a branch.
     */
    public static function getForBranch(int $branchId): self
    {
        // If the branch does not exist, return an unsaved default settings
        // object instead of attempting to create a DB row (avoids FK errors).
        if (!Branch::find($branchId)) {
            $instance = new self();
            $instance->branch_id = $branchId;
            $instance->early_clockout_override = false;
            return $instance;
        }

        $settings = self::where('branch_id', $branchId)->first();

        if (!$settings) {
            $settings = self::create([
                'branch_id' => $branchId,
                'early_clockout_override' => false,
            ]);
        }

        return $settings;
    }
}
