<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerType extends Model
{
    protected $fillable = [
        'name',
        'custom_id',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get all customers of this type
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'customer_type_id');
    }

    /**
     * Scope to get only active customer types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get customer type by name (for backward compatibility)
     */
    public static function getByName($name)
    {
        return static::where('name', $name)->first();
    }

    /**
     * Get custom ID for reference number generation
     */
    public function getCustomIdAttribute($value)
    {
        return strtoupper($value);
    }

    /**
     * Set custom ID in uppercase
     */
    public function setCustomIdAttribute($value)
    {
        $this->attributes['custom_id'] = strtoupper($value);
    }
}
