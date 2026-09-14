<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Schedule Time Out
    |--------------------------------------------------------------------------
    |
    | The default scheduled time out for staff. Staff cannot clock out before
    | this time unless override is enabled by Owner or HR for their branch.
    | Format: H:i:s (24-hour format)
    |
    */
    'default_time_out' => env('ATTENDANCE_DEFAULT_TIME_OUT', '22:00:00'),

    'default_auto_clockout_time' => env('ATTENDANCE_DEFAULT_AUTO_CLOCKOUT_TIME', '22:00:00'),

    'auto_clockout_grace_minutes' => env('ATTENDANCE_AUTO_CLOCKOUT_GRACE_MINUTES', 5),

    /*
    |--------------------------------------------------------------------------
    | Default Schedule Time In
    |--------------------------------------------------------------------------
    |
    | The default scheduled time in for staff.
    |
    */
    'default_time_in' => env('ATTENDANCE_DEFAULT_TIME_IN', '08:00:00'),
];
