<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_number',
        'reference_number',
        'meter_number',
        'title',
        'first_name',
        'last_name',
        'profile_photo',
        'email',
        'phone',
        'phone_two',
        'nic',
        'epf_number',
        'address',
        'city',
        'postal_code',
        'status',
        'customer_type_id',
        'division_id',
        'guarantor_id',
        'connection_date',
        'deposit_amount',
        'notes',
        'billing_day',
        'next_billing_date',
        'auto_billing_enabled'
    ];

    protected $casts = [
        'connection_date' => 'date',
        'next_billing_date' => 'date',
        'deposit_amount' => 'decimal:2',
        'auto_billing_enabled' => 'boolean'
    ];

    // Relationships
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function customerType(): BelongsTo
    {
        return $this->belongsTo(CustomerType::class);
    }

    public function guarantor(): BelongsTo
    {
        return $this->belongsTo(Guarantor::class);
    }

    public function waterMeters(): HasMany
    {
        return $this->hasMany(WaterMeter::class);
    }

    public function meterReadings(): HasMany
    {
        return $this->hasManyThrough(MeterReading::class, WaterMeter::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        $title = $this->title ? $this->title . ' ' : '';
        return $title . $this->first_name . ' ' . $this->last_name;
    }

    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo) {
            return asset('storage/' . $this->profile_photo);
        }
        
        // Return the default profile picture
        return asset('images/profile.png');
    }

    public function getActiveWaterMeterAttribute(): ?WaterMeter
    {
        return $this->waterMeters()->where('status', 'active')->first();
    }

    public function getWaterMeterAttribute(): ?WaterMeter
    {
        return $this->getActiveWaterMeterAttribute();
    }

    public function getLatestReadingAttribute(): ?MeterReading
    {
        $activeMeter = $this->getActiveWaterMeterAttribute();
        return $activeMeter ? $activeMeter->meterReadings()->latest('reading_date')->first() : null;
    }

    public function getFullAddressAttribute(): string
    {
        return $this->address . ($this->city ? ', ' . $this->city : '');
    }

    /**
     * Get name for activity logging
     */
    public function getLogName(): string
    {
        return "{$this->full_name} ({$this->account_number})";
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('customer_type', $type);
    }

    // Helper methods
    public function getOutstandingBalance(): float
    {
        return $this->bills()
            ->whereIn('status', ['generated', 'sent', 'overdue'])
            ->sum('balance_amount');
    }

    public function getLastBill(): ?Bill
    {
        return $this->bills()->latest('bill_date')->first();
    }

    public function getCurrentReading(): ?MeterReading
    {
        $activeMeter = $this->getActiveWaterMeterAttribute();
        return $activeMeter ? $activeMeter->meterReadings()->latest('reading_date')->first() : null;
    }

    /**
     * Generate unique account number with numbers-only format
     * Format: AC{year_short}{random_numbers} (e.g., AC25123456789)
     */
    public static function generateAccountNumber(): string
    {
        $yearShort = substr(date('Y'), -2); // Last 2 digits of year (e.g., 25 for 2025)
        
        do {
            // Generate 8 random numbers for uniqueness
            $randomNumbers = '';
            for ($i = 0; $i < 8; $i++) {
                $randomNumbers .= rand(0, 9);
            }
            
            $accountNumber = "AC{$yearShort}{$randomNumbers}";
            
            // Check if this account number already exists
            $exists = self::where('account_number', $accountNumber)->exists();
            
        } while ($exists); // Keep generating until we get a unique one
        
        return $accountNumber;
    }

    /**
     * Generate reference number based on division and customer type
     * Format: CP/NE/DN/division_custom_id/type_custom_id/auto_increment_number
     */
    public static function generateReferenceNumber($divisionId, $customerTypeId): string
    {
        $division = Division::find($divisionId);
        $customerType = CustomerType::find($customerTypeId);
        
        // Use custom IDs if available, otherwise fallback to name prefixes
        $divisionPrefix = $division && $division->custom_id 
            ? $division->custom_id 
            : ($division ? substr($division->name, 0, 3) : 'UNK');
            
        $typePrefix = $customerType && $customerType->custom_id 
            ? $customerType->custom_id 
            : 'GEN';

        // Build the base prefix with CP/NE/DN/ format
        $basePrefix = 'CP/NE/DN/' . strtoupper($divisionPrefix) . '/' . strtoupper($typePrefix) . '/';
        
        // Get last customer with similar prefix pattern
        $lastCustomer = self::where('reference_number', 'like', $basePrefix . '%')
            ->orderBy('reference_number', 'desc')
            ->first();

        if ($lastCustomer) {
            // Extract the number part after the last slash
            $parts = explode('/', $lastCustomer->reference_number);
            $lastNumber = (int)end($parts);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $basePrefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate next billing date based on billing day
     */
    public function calculateNextBillingDate(): ?string
    {
        if (!$this->billing_day) {
            return null;
        }

        $today = now();
        $currentMonth = $today->month;
        $currentYear = $today->year;
        
        // Try to create billing date for current month
        try {
            $billingDate = \Carbon\Carbon::create($currentYear, $currentMonth, $this->billing_day);
            
            // If billing date has passed this month, move to next month
            if ($billingDate->isPast()) {
                $billingDate = $billingDate->addMonth();
            }
            
            return $billingDate->format('Y-m-d');
        } catch (\Exception $e) {
            // If day doesn't exist in current month (e.g., 31st in February), use last day of month
            $billingDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->endOfMonth();
            
            if ($billingDate->isPast()) {
                $billingDate = $billingDate->addMonth()->endOfMonth();
            }
            
            return $billingDate->format('Y-m-d');
        }
    }

    /**
     * Update next billing date
     */
    public function updateNextBillingDate(): void
    {
        $this->update([
            'next_billing_date' => $this->calculateNextBillingDate()
        ]);
    }

    /**
     * Check if customer is due for billing
     */
    public function isDueForBilling(): bool
    {
        if (!$this->auto_billing_enabled || !$this->next_billing_date) {
            return false;
        }

        return now()->gte($this->next_billing_date);
    }

    /**
     * Get billing day display text
     */
    public function getBillingDayTextAttribute(): string
    {
        if (!$this->billing_day) {
            return 'Not set';
        }

        $suffix = 'th';
        if ($this->billing_day == 1 || $this->billing_day == 21 || $this->billing_day == 31) {
            $suffix = 'st';
        } elseif ($this->billing_day == 2 || $this->billing_day == 22) {
            $suffix = 'nd';
        } elseif ($this->billing_day == 3 || $this->billing_day == 23) {
            $suffix = 'rd';
        }

        return $this->billing_day . $suffix . ' of each month';
    }

    /**
     * Boot method to auto-generate account number and reference number
     */
    protected static function boot()
    {
        parent::boot();

        // Prevent deletion of customers with active meters
        static::deleting(function ($customer) {
            $activeMeters = $customer->waterMeters()->count();
            if ($activeMeters > 0) {
                throw new \Exception("Cannot delete customer with {$activeMeters} water meters. Please reassign or delete meters first.");
            }
        });

        static::creating(function ($customer) {
            // Auto-generate account number if not provided
            if (empty($customer->account_number)) {
                $customer->account_number = self::generateAccountNumber();
            }
            
            // Auto-generate reference number if not provided and required fields are present
            if (empty($customer->reference_number) && $customer->division_id && $customer->customer_type_id) {
                $customer->reference_number = self::generateReferenceNumber(
                    $customer->division_id, 
                    $customer->customer_type_id
                );
            }

            // Set default billing day from system configuration if not provided
            if (empty($customer->billing_day)) {
                $customer->billing_day = \App\Models\SystemConfiguration::getDefaultBillingDay();
            }

            // Set default auto-billing status from system configuration if not provided
            if (is_null($customer->auto_billing_enabled)) {
                $customer->auto_billing_enabled = \App\Models\SystemConfiguration::getDefaultAutoBilling();
            }

            // Calculate initial next billing date
            if (empty($customer->next_billing_date) && $customer->billing_day) {
                $customer->next_billing_date = $customer->calculateNextBillingDate();
            }
        });

        static::updating(function ($customer) {
            // Recalculate next billing date if billing day changed
            if ($customer->isDirty('billing_day') && $customer->billing_day) {
                $customer->next_billing_date = $customer->calculateNextBillingDate();
            }
        });
    }
}
