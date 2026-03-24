<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dish extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'created_by',
        'branch_id',
        'status'
    ];

    public function ingredients(): HasMany
    {
        return $this->hasMany(DishIngredient::class);
    }
}
