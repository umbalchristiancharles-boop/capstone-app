<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCommentFlag extends Model
{
    protected $table = 'product_comment_flags';

    protected $fillable = [
        'product_comment_id',
        'admin_user_id',
        'reason',
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(ProductComment::class, 'product_comment_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
