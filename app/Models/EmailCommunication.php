<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCommunication extends Model
{
    protected $table = 'email_communications';

    protected $fillable = [
        'customer_report_id',
        'sender_email',
        'sender_name',
        'recipient_email',
        'recipient_name',
        'subject',
        'message',
        'direction',
        'status',
        'error_message',
        'read_at',
        'sent_by',
        'message_id',
        'in_reply_to',
        'references',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** Relationship: Email communication belongs to a customer report */
    public function customerReport(): BelongsTo
    {
        return $this->belongsTo(CustomerReport::class, 'customer_report_id');
    }

    /** Relationship: Email sent by a user (admin/staff) */
    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}