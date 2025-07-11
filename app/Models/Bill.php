<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'water_meter_id',
        'meter_reading_id',
        'bill_number',
        'bill_date',
        'due_date',
        'billing_period_from',
        'billing_period_to',
        'previous_reading',
        'current_reading',
        'consumption',
        'water_charges',
        'fixed_charges',
        'service_charges',
        'late_fees',
        'taxes',
        'adjustments',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'status',
        'rate_breakdown',
        'notes',
        'sent_at',
        'paid_at'
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'billing_period_from' => 'date',
        'billing_period_to' => 'date',
        'previous_reading' => 'decimal:2',
        'current_reading' => 'decimal:2',
        'consumption' => 'decimal:2',
        'water_charges' => 'decimal:2',
        'fixed_charges' => 'decimal:2',
        'service_charges' => 'decimal:2',
        'late_fees' => 'decimal:2',
        'taxes' => 'decimal:2',
        'adjustments' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'rate_breakdown' => 'array',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime'
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function waterMeter(): BelongsTo
    {
        return $this->belongsTo(WaterMeter::class);
    }

    public function meterReading(): BelongsTo
    {
        return $this->belongsTo(MeterReading::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeGenerated($query)
    {
        return $query->where('status', 'generated');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['generated', 'sent', 'overdue'])
            ->where('balance_amount', '>', 0);
    }

    public function scopeDueToday($query)
    {
        return $query->where('due_date', Carbon::today());
    }

    public function scopeOverdueToday($query)
    {
        return $query->where('due_date', '<', Carbon::today())
            ->whereIn('status', ['generated', 'sent']);
    }

    // Model Events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bill) {
            if (empty($bill->bill_number)) {
                $bill->bill_number = $bill->generateBillNumber();
            }
        });

        static::saving(function ($bill) {
            $bill->calculateTotalAmount();
            $bill->updateBalance();
            $bill->updateStatusBasedOnPayment();
        });
    }

    // Helper methods
    public function generateBillNumber(): string
    {
        $year = $this->bill_date ? $this->bill_date->year : Carbon::now()->year;
        $month = $this->bill_date ? $this->bill_date->format('m') : Carbon::now()->format('m');
        
        $lastBill = static::where('bill_number', 'like', "WB{$year}{$month}%")
            ->latest('bill_number')
            ->first();

        if ($lastBill) {
            $lastNumber = intval(substr($lastBill->bill_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "WB{$year}{$month}{$newNumber}";
    }

    public function calculateTotalAmount(): void
    {
        $this->total_amount = $this->water_charges + 
                             $this->fixed_charges + 
                             $this->service_charges + 
                             $this->late_fees + 
                             $this->taxes + 
                             $this->adjustments;
    }

    public function updateBalance(): void
    {
        $this->balance_amount = $this->total_amount - $this->paid_amount;
    }

    protected function updateStatusBasedOnPayment(): void
    {
        if ($this->balance_amount <= 0 && $this->paid_amount > 0) {
            $this->status = 'paid';
            if (!$this->paid_at) {
                $this->paid_at = Carbon::now();
            }
        } elseif ($this->due_date < Carbon::now() && $this->balance_amount > 0) {
            $this->status = 'overdue';
        }
    }

    public function generateFromReading(MeterReading $reading): self
    {
        $customer = $reading->waterMeter->customer;
        $charges = Rate::calculateCharges(
            $customer->customer_type, 
            $reading->consumption, 
            $this->bill_date ?? Carbon::now()
        );

        $this->customer_id = $customer->id;
        $this->water_meter_id = $reading->water_meter_id;
        $this->meter_reading_id = $reading->id;
        $this->bill_date = Carbon::now();
        $this->due_date = Carbon::now()->addDays(30);
        $this->billing_period_from = $reading->reading_date->startOfMonth();
        $this->billing_period_to = $reading->reading_date->endOfMonth();
        $this->previous_reading = $reading->previous_reading;
        $this->current_reading = $reading->current_reading;
        $this->consumption = $reading->consumption;
        $this->water_charges = $charges['water_charges'];
        $this->fixed_charges = $charges['fixed_charges'];
        $this->rate_breakdown = $charges['breakdown'];
        $this->status = 'draft';

        return $this;
    }

    public function recordPayment(float $amount, string $paymentMethod = 'cash'): bool
    {
        if ($amount <= 0 || $amount > $this->balance_amount) {
            return false;
        }

        $this->paid_amount += $amount;
        $this->save();

        return true;
    }

    public function markAsSent(): bool
    {
        $this->status = 'sent';
        $this->sent_at = Carbon::now();
        return $this->save();
    }

    public function addLateFees(float $amount): bool
    {
        $this->late_fees += $amount;
        return $this->save();
    }

    public function addAdjustment(float $amount, string $reason = null): bool
    {
        $this->adjustments += $amount;
        if ($reason && $this->notes) {
            $this->notes .= "\nAdjustment: {$reason}";
        } elseif ($reason) {
            $this->notes = "Adjustment: {$reason}";
        }
        return $this->save();
    }

    public function isOverdue(): bool
    {
        return $this->due_date < Carbon::now() && $this->balance_amount > 0;
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid' || $this->balance_amount <= 0;
    }

    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return Carbon::now()->diffInDays($this->due_date);
    }

    public function getBillingPeriodDays(): int
    {
        return $this->billing_period_from->diffInDays($this->billing_period_to) + 1;
    }
}
