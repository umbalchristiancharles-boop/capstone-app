<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendance';
    protected $fillable = ['user_id', 'date', 'time_in', 'time_out', 'hours_worked', 'status', 'notes', 'face_image', 'confirmed', 'confirmed_by', 'confirmed_at'];
    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'confirmed' => 'boolean',
        'confirmed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function hoursWorkedInHours()
    {
        return round($this->hours_worked / 60, 2);
    }
}
