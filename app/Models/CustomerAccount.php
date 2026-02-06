<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAccount extends Model
{
    protected $table = 'customer_accounts';

    protected $fillable = [
        'user_id',
        'email',
        'full_name',
        'phone_number',
        'address',
        'city',
        'province',
        'postal_code',
        'total_comments',
        'total_ratings',
        'last_activity_at',
        'status',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Customer account belongs to a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
