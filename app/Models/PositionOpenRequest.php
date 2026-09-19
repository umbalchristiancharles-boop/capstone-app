<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PositionOpenRequest extends Model
{
    protected $table = 'position_open_requests';

    protected $fillable = [
        'position_id',
        'branch_id',
        'requested_by_user_id',
        'quantity',
        'notes',
        'account_type',
        'account_config',
        'status',
        'approved_by_user_id',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'account_config' => 'array',
    ];

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }
}

