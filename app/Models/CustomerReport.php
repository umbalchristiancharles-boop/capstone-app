<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerReport extends Model
{
    protected $table = 'customer_reports';

    protected $fillable = [
        'customer_account_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'subject',
        'message',
        'status',
        'admin_notes',
        'assigned_to',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** Relationship: Customer report belongs to a customer account */
    public function customerAccount(): BelongsTo
    {
        return $this->belongsTo(CustomerAccount::class, 'customer_account_id');
    }

    /** Relationship: Customer report assigned to a user (admin/staff) */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** Relationship: Customer report has many email communications */
    public function emailCommunications()
    {
        return $this->hasMany(EmailCommunication::class, 'customer_report_id');
    }
}
