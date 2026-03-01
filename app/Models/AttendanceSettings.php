<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
