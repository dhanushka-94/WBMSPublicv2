<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class MeterReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'water_meter_id',
        'reading_date',
        'current_reading',
        'previous_reading',
        'consumption',
        'reading_type',
        'reader_name',
        'reader_id',
        'notes',
        'is_billable',
        'status',
        'meter_condition',
        'photo_path',
        'gps_latitude',
        'gps_longitude',
        'submitted_via',
        'offline_timestamp'
    ];

    protected $casts = [
        'reading_date' => 'date',
        'current_reading' => 'decimal:2',
        'previous_reading' => 'decimal:2',
        'consumption' => 'decimal:2',
        'is_billable' => 'boolean',
        'gps_latitude' => 'decimal:8',
        'gps_longitude' => 'decimal:8',
        'offline_timestamp' => 'datetime'
    ];

    // Relationships
    public function waterMeter(): BelongsTo
    {
        return $this->belongsTo(WaterMeter::class);
    }

    public function customer(): HasOneThrough
    {
        return $this->hasOneThrough(Customer::class, WaterMeter::class, 'id', 'id', 'water_meter_id', 'customer_id');
    }

    public function reader(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'reader_id');
    }

    public function bill(): HasOne
    {
        return $this->hasOne(Bill::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeBilled($query)
    {
        return $query->where('status', 'billed');
    }

    public function scopeBillable($query)
    {
        return $query->where('is_billable', true);
    }

    public function scopeByReadingType($query, $type)
    {
        return $query->where('reading_type', $type);
    }

    public function scopeInDateRange($query, $from, $to)
    {
        return $query->whereBetween('reading_date', [$from, $to]);
    }

    // Model Events
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($reading) {
            $reading->calculateConsumption();
        });

        static::saved(function ($reading) {
            $reading->updateMeterCurrentReading();
        });
    }

    // Helper methods
    public function calculateConsumption(): void
    {
        if ($this->previous_reading !== null) {
            $consumption = $this->current_reading - $this->previous_reading;
            $this->consumption = max(0, $consumption); // Ensure non-negative consumption
        } else {
            // If no previous reading, get it from the previous record
            $previousReading = $this->waterMeter
                ->meterReadings()
                ->where('reading_date', '<', $this->reading_date)
                ->latest('reading_date')
                ->first();

            if ($previousReading) {
                $this->previous_reading = $previousReading->current_reading;
                $consumption = $this->current_reading - $this->previous_reading;
                $this->consumption = max(0, $consumption);
            } else {
                // First reading for this meter
                $this->previous_reading = $this->waterMeter->initial_reading;
                $consumption = $this->current_reading - $this->previous_reading;
                $this->consumption = max(0, $consumption);
            }
        }
    }

    protected function updateMeterCurrentReading(): void
    {
        if ($this->waterMeter) {
            $this->waterMeter->updateCurrentReading($this->current_reading);
        }
    }

    public function verify(): bool
    {
        $this->status = 'verified';
        return $this->save();
    }

    public function markAsBilled(): bool
    {
        $this->status = 'billed';
        return $this->save();
    }

    public function isEstimated(): bool
    {
        return $this->reading_type === 'estimated';
    }

    public function isActual(): bool
    {
        return $this->reading_type === 'actual';
    }

    public function getConsumptionInLiters(): float
    {
        // Assuming readings are in cubic meters, convert to liters
        return $this->consumption * 1000;
    }

    public function getConsumptionForPeriod(): float
    {
        // Apply meter multiplier if needed
        return $this->consumption * ($this->waterMeter->multiplier ?? 1);
    }
}
