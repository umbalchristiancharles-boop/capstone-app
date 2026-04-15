<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Hash;  // ← FIXED: Import Hash
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string|null $full_name
 * @property string $role
 * @property int|null $branch_id
 * @property string|null $avatar_url
 * @property string|null $phone_number
 * @property string|null $address
 * @property float|null $latitude
 * @property float|null $longitude
 * @property bool $is_active
 * @property bool $must_change_password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class User extends Authenticatable implements CanResetPassword
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'full_name',
        'role',
        'department',
        'permissions',
        'branch_id',
        'avatar_url',
        'phone_number',
        'address',
        'latitude',
        'longitude',
        'is_active',
        'must_change_password',
        'requires_setup',
        'required_setup_type',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'must_change_password' => 'boolean',
        'requires_setup' => 'boolean',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'updated_at' => 'datetime',
        'permissions' => 'array',
    ];

    /**
     * Relationship: User belongs to a Branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Relationship: User has one profile
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    /**
     * Relationship: Supplier has many supply orders (if user is a supplier)
     */
    public function supplierOrders(): HasMany
    {
        return $this->hasMany(SupplierOrder::class, 'supplier_id');
    }

    /**
     * Relationship: User has one customer account
     */
    public function customerAccount(): HasOne
    {
        return $this->hasOne(CustomerAccount::class, 'user_id');
    }

    /**
     * Return the password for the Auth system.
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Hash the password when setting it.
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    // Required for Password Reset
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }

    public function getEmailForPasswordReset()
    {
        return $this->email;
    }
}
