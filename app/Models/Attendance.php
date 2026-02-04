<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendance';
    protected $fillable = ['user_id', 'date', 'time_in', 'time_out', 'hours_worked', 'status', 'notes'];
    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime:H:i',
        'time_out' => 'datetime:H:i',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hoursWorkedInHours()
    {
        return round($this->hours_worked / 60, 2);
    }
}
