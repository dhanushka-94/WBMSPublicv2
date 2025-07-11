<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Rate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'customer_type',
        'tier_from',
        'tier_to',
        'rate_per_unit',
        'fixed_charge',
        'is_active',
        'effective_from',
        'effective_to',
        'description'
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'rate_per_unit' => 'decimal:4',
        'fixed_charge' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function customerType()
    {
        return $this->belongsTo(CustomerType::class, 'customer_type', 'name');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEffective($query, $date = null)
    {
        $date = $date ?? Carbon::now();
        return $query->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $date);
            });
    }

    public function scopeForCustomerType($query, $customerType)
    {
        return $query->where('customer_type', $customerType);
    }

    // Helper methods
    public static function getActiveRatesForCustomer($customerType, $date = null): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()
            ->effective($date)
            ->forCustomerType($customerType)
            ->orderBy('tier_from')
            ->get();
    }

    public static function calculateCharges($customerType, $consumption, $date = null): array
    {
        $rates = static::getActiveRatesForCustomer($customerType, $date);
        $charges = [];
        $totalWaterCharges = 0;
        $totalFixedCharges = 0;
        $remainingConsumption = $consumption;

        // Handle special case for 0 consumption (fixed charge only)
        if ($consumption == 0) {
            $fixedRate = $rates->where('tier_from', 0)->where('tier_to', 0)->first();
            if ($fixedRate) {
                $totalFixedCharges = $fixedRate->fixed_charge;
                $charges[] = [
                    'tier_name' => $fixedRate->name,
                    'tier_from' => 0,
                    'tier_to' => 0,
                    'consumption' => 0,
                    'rate_per_unit' => 0,
                    'charge' => 0,
                    'fixed_charge' => $fixedRate->fixed_charge
                ];
            }
            
            return [
                'breakdown' => $charges,
                'water_charges' => $totalWaterCharges,
                'fixed_charges' => $totalFixedCharges,
                'total_charges' => $totalWaterCharges + $totalFixedCharges
            ];
        }

        // Process tiered rates for consumption > 0
        foreach ($rates as $rate) {
            if ($remainingConsumption <= 0) break;
            
            // Skip the fixed charge tier (0-0) for consumption > 0
            if ($rate->tier_from == 0 && $rate->tier_to == 0) {
                continue;
            }

            $tierConsumption = 0;
            
            if ($rate->tier_to === null) {
                // Unlimited tier (26+ units)
                $tierConsumption = $remainingConsumption;
            } else {
                // Calculate consumption for this tier
                $tierStart = max($rate->tier_from, $consumption - $remainingConsumption + 1);
                $tierEnd = min($rate->tier_to, $consumption);
                
                if ($tierStart <= $tierEnd) {
                    $tierConsumption = $tierEnd - $tierStart + 1;
                }
            }

            if ($tierConsumption > 0) {
                $tierCharge = $tierConsumption * $rate->rate_per_unit;
                $totalWaterCharges += $tierCharge;

                $charges[] = [
                    'tier_name' => $rate->name,
                    'tier_from' => $rate->tier_from,
                    'tier_to' => $rate->tier_to,
                    'consumption' => $tierConsumption,
                    'rate_per_unit' => $rate->rate_per_unit,
                    'charge' => $tierCharge,
                    'fixed_charge' => 0
                ];

                $remainingConsumption -= $tierConsumption;
            }
        }

        return [
            'breakdown' => $charges,
            'water_charges' => $totalWaterCharges,
            'fixed_charges' => $totalFixedCharges,
            'total_charges' => $totalWaterCharges + $totalFixedCharges
        ];
    }

    public function isEffective($date = null): bool
    {
        $date = $date ?? Carbon::now();
        return $this->effective_from <= $date && 
               ($this->effective_to === null || $this->effective_to >= $date);
    }
}
