<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductComment extends Model
{
    protected $fillable = [
        'product_id',
        'parent_comment_id',
        'author',
        'text',
        'rating',
        'ip_address',
        'user_id',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductComment::class, 'parent_comment_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ProductComment::class, 'parent_comment_id')->latest('created_at');
    }
}
