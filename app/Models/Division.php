<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
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
     * Get all customers in this division
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Scope to get only active divisions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
