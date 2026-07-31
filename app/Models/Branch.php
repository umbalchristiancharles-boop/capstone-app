<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $table = 'branches';

    protected $fillable = [
        'code',
        'name',
        'address',
        'latitude',
        'longitude',
        'square_meters',
        'geofencing_radius',
        'is_active',
        'is_main_branch',
        'approval_status',
        'requested_by',
        'finance_confirmed_by',
        'finance_confirmed_at',
        'approved_by',
        'approved_at',
        'rejected_at',
        'budget',
        'default_password',
        'default_password_updated_at',
        'permit_bills',
        'construction_costs',
        'equipment_costs',
        'total_investment',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_main_branch' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'square_meters' => 'float',
            'geofencing_radius' => 'float',
            'finance_confirmed_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'default_password_updated_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'permit_bills' => 'array',
            'construction_costs' => 'array',
            'equipment_costs' => 'array',
            'total_investment' => 'decimal:2',
        ];
    }

    /**
     * Relationship: Branch has many users
     */
    public function users()
    {
        return $this->hasMany(User::class, 'branch_id');
    }

    /**
     * Check if the branch's current password is from today
     */
    public function isPasswordFromToday(): bool
    {
        if ($this->default_password_updated_at === null) {
            return false;
        }

        return $this->default_password_updated_at->isToday();
    }
}
