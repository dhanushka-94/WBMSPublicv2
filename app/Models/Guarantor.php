<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guarantor extends Model
{
    protected $fillable = [
        'guarantor_id',
        'first_name',
        'last_name',
        'nic',
        'phone',
        'email',
        'address',
        'relationship',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get all customers for this guarantor
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Scope to get only active guarantors
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Generate guarantor ID
     */
    public static function generateGuarantorId(): string
    {
        $lastGuarantor = self::latest('id')->first();
        $nextNumber = $lastGuarantor ? (int)substr($lastGuarantor->guarantor_id, 1) + 1 : 1;
        return 'G' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Boot method to auto-generate guarantor ID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($guarantor) {
            if (empty($guarantor->guarantor_id)) {
                $guarantor->guarantor_id = self::generateGuarantorId();
            }
        });
    }
}
