<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table = 'positions';

    protected $fillable = [
        'name',
        'description',
        'department',
        'is_active',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function openPositionRequests()
    {
        return $this->hasMany(PositionOpenRequest::class, 'position_id');
    }
}


