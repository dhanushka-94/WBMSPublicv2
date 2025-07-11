<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaterMeter extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'meter_number',
        'meter_brand',
        'meter_model',
        'meter_size',
        'meter_type',
        'installation_date',
        'last_maintenance_date',
        'next_maintenance_date',
        'initial_reading',
        'current_reading',
        'status',
        'multiplier',
        'location_notes',
        'latitude',
        'longitude',
        'address',
        'google_place_id',
        'location_metadata',
        'notes'
    ];

    protected $casts = [
        'installation_date' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'initial_reading' => 'decimal:2',
        'current_reading' => 'decimal:2',
        'multiplier' => 'decimal:4',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'location_metadata' => 'array'
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function meterReadings(): HasMany
    {
        return $this->hasMany(MeterReading::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDueForMaintenance($query)
    {
        return $query->whereNotNull('next_maintenance_date')
            ->where('next_maintenance_date', '<=', now());
    }

    // Helper methods
    public function getLatestReading(): ?MeterReading
    {
        return $this->meterReadings()->latest('reading_date')->first();
    }

    public function getPreviousReading(): ?MeterReading
    {
        return $this->meterReadings()
            ->latest('reading_date')
            ->skip(1)
            ->first();
    }

    public function getTotalConsumption(): float
    {
        return $this->current_reading - $this->initial_reading;
    }

    public function getMonthlyConsumption($month = null, $year = null): float
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        return $this->meterReadings()
            ->whereMonth('reading_date', $month)
            ->whereYear('reading_date', $year)
            ->sum('consumption');
    }

    public function updateCurrentReading(float $newReading): bool
    {
        if ($newReading >= $this->current_reading) {
            $this->current_reading = $newReading;
            return $this->save();
        }
        return false;
    }

    public function isDueForMaintenance(): bool
    {
        return $this->next_maintenance_date && 
               $this->next_maintenance_date <= now();
    }

    public function getReadingHistory($limit = 12): \Illuminate\Database\Eloquent\Collection
    {
        return $this->meterReadings()
            ->latest('reading_date')
            ->limit($limit)
            ->get();
    }

    public function getAverageMonthlyConsumption($months = 12): float
    {
        $readings = $this->meterReadings()
            ->where('reading_date', '>=', now()->subMonths($months))
            ->where('consumption', '>', 0)
            ->avg('consumption');

        return $readings ?? 0;
    }

    // Location helper methods
    public function hasLocation(): bool
    {
        return $this->latitude && $this->longitude;
    }

    public function getGoogleMapsUrl(): string
    {
        if (!$this->hasLocation()) {
            return '#';
        }
        
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    public function getGoogleMapsEmbedUrl(): string
    {
        if (!$this->hasLocation()) {
            return '';
        }
        
        return "https://www.google.com/maps/embed/v1/place?key=YOUR_API_KEY&q={$this->latitude},{$this->longitude}";
    }

    public function getDistanceFrom($latitude, $longitude): float
    {
        if (!$this->hasLocation()) {
            return 0;
        }

        $earthRadius = 6371; // Earth's radius in kilometers

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    public function scopeNearLocation($query, $latitude, $longitude, $radius = 10)
    {
        return $query->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw("
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + sin(radians(?)) * 
                sin(radians(latitude)))) <= ?
            ", [$latitude, $longitude, $latitude, $radius]);
    }
}
