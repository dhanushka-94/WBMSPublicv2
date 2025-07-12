<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

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

    public function scopeWithValidCustomer($query)
    {
        return $query->whereNotNull('customer_id')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('customers')
                  ->whereRaw('customers.id = water_meters.customer_id');
            });
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

    // QR Code Methods
    public function generateQrCode($size = 200, $format = 'svg')
    {
        try {
            // Simple QR code containing only the meter number
            $qrCode = QrCode::format($format)
                ->size($size)
                ->backgroundColor(255, 255, 255)
                ->color(0, 0, 0)
                ->margin(1)
                ->generate($this->meter_number);
            
            return $qrCode;
        } catch (\Exception $e) {
            // Fallback based on format
            \Log::warning('QR code generation failed: ' . $e->getMessage());
            
            if ($format === 'png') {
                // Create a simple PNG image with text
                $image = imagecreate($size, $size);
                $bgColor = imagecolorallocate($image, 240, 240, 240);
                $textColor = imagecolorallocate($image, 51, 51, 51);
                
                // Fill background
                imagefill($image, 0, 0, $bgColor);
                
                // Add text
                $text = 'QR Code Failed';
                $fontSize = 12;
                $x = ($size - strlen($text) * $fontSize * 0.6) / 2;
                $y = $size / 2;
                imagestring($image, 3, $x, $y - 10, $text, $textColor);
                
                $meterText = 'Meter: ' . $this->meter_number;
                $x2 = ($size - strlen($meterText) * 10 * 0.6) / 2;
                imagestring($image, 2, $x2, $y + 10, $meterText, $textColor);
                
                // Capture PNG data
                ob_start();
                imagepng($image);
                $pngData = ob_get_contents();
                ob_end_clean();
                
                imagedestroy($image);
                return $pngData;
            } else {
                // SVG fallback
                return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '">
                    <rect width="' . $size . '" height="' . $size . '" fill="#f0f0f0"/>
                    <text x="50%" y="45%" text-anchor="middle" dy="0.35em" font-family="Arial, sans-serif" font-size="14" fill="#333">
                        QR Code Failed
                    </text>
                    <text x="50%" y="55%" text-anchor="middle" dy="0.35em" font-family="Arial, sans-serif" font-size="12" fill="#666">
                        Meter: ' . $this->meter_number . '
                    </text>
                </svg>';
            }
        }
    }

    public function getQrCodePath($size = 200, $format = 'svg')
    {
        $directory = 'qr-codes/meters';
        $filename = "meter_{$this->meter_number}_{$size}.{$format}";
        $path = "{$directory}/{$filename}";

        // Check if QR code already exists
        if (!Storage::disk('public')->exists($path)) {
            // Generate QR code
            $qrCode = $this->generateQrCode($size, $format);
            
            // Ensure directory exists
            Storage::disk('public')->makeDirectory($directory);
            
            // Save QR code
            Storage::disk('public')->put($path, $qrCode);
        }

        return $path;
    }

    public function getQrCodeUrl($size = 200, $format = 'svg')
    {
        $path = $this->getQrCodePath($size, $format);
        return Storage::disk('public')->url($path);
    }

    public function getQrCodeBase64($size = 200, $format = 'svg')
    {
        $qrCode = $this->generateQrCode($size, $format);
        return base64_encode($qrCode);
    }

    public function getQrCodeData()
    {
        return $this->meter_number;
    }

    public static function findByQrCode($qrCodeData)
    {
        // Since QR code now contains only the meter number, search by meter number
        return self::where('meter_number', trim($qrCodeData))->first();
    }

    public function deleteQrCode()
    {
        $directory = 'qr-codes/meters';
        
        // Delete old QR codes with both naming schemes (ID and meter number)
        $files = Storage::disk('public')->files($directory);
        foreach ($files as $file) {
            // Delete files with old naming scheme (meter ID)
            if (str_contains($file, "meter_{$this->id}_")) {
                Storage::disk('public')->delete($file);
            }
            // Delete files with new naming scheme (meter number)
            if (str_contains($file, "meter_{$this->meter_number}_")) {
                Storage::disk('public')->delete($file);
            }
        }
    }

    // Override delete to clean up QR codes
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($meter) {
            $meter->deleteQrCode();
        });
    }
}
