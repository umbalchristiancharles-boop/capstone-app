<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'from_user_id',
        'to_user_id',
        'body',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'delivered_at',
        'read_at',
    ];

    protected $dates = ['delivered_at', 'read_at', 'created_at', 'updated_at'];

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
