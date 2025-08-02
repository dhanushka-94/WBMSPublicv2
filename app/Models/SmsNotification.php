<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SmsNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'bill_id',
        'phone_number',
        'message',
        'type',
        'status',
        'response_data',
        'http_status',
        'retry_count',
        'sent_at',
        'scheduled_at',
        'error_message'
    ];

    protected $casts = [
        'response_data' => 'array',
        'sent_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'retry_count' => 'integer',
        'http_status' => 'integer'
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeScheduledFor($query, Carbon $date)
    {
        return $query->whereDate('scheduled_at', $date);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    // Methods
    public function markAsSent(array $responseData = [], int $httpStatus = 200): bool
    {
        return $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'response_data' => $responseData,
            'http_status' => $httpStatus,
            'error_message' => null
        ]);
    }

    public function markAsFailed(string $errorMessage, array $responseData = [], int $httpStatus = 0): bool
    {
        return $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'response_data' => $responseData,
            'http_status' => $httpStatus,
            'retry_count' => $this->retry_count + 1
        ]);
    }

    public function canRetry(): bool
    {
        $maxRetries = config('sms.notifications.retry_attempts', 3);
        return $this->status === 'failed' && $this->retry_count < $maxRetries;
    }

    public function isDue(): bool
    {
        if (!$this->scheduled_at) {
            return true; // Send immediately if no schedule
        }

        return $this->scheduled_at <= now();
    }

    public function getFormattedPhoneAttribute(): string
    {
        // Format phone number for display (e.g., 94712345678 -> +94 71 234 5678)
        $phone = $this->phone_number;
        if (strlen($phone) === 12 && substr($phone, 0, 2) === '94') {
            return '+94 ' . substr($phone, 2, 2) . ' ' . substr($phone, 4, 3) . ' ' . substr($phone, 7);
        }
        return $phone;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>',
            'sent' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Sent</span>',
            'failed' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Failed</span>',
            'cancelled' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Cancelled</span>',
            default => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>'
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'new_bill' => 'New Bill',
            'due_reminder' => 'Due Reminder',
            'overdue_alert' => 'Overdue Alert',
            'payment_confirmation' => 'Payment Confirmation',
            'late_fee_notice' => 'Late Fee Notice',
            'service_disconnection' => 'Service Disconnection',
            'meter_reading_reminder' => 'Meter Reading Reminder',
            'maintenance_notice' => 'Maintenance Notice',
            'custom' => 'Custom Message',
            default => ucfirst(str_replace('_', ' ', $this->type))
        };
    }

    // Static methods
    public static function createForBill(Bill $bill, string $type, string $message, ?Carbon $scheduledAt = null): self
    {
        return self::create([
            'customer_id' => $bill->customer_id,
            'bill_id' => $bill->id,
            'phone_number' => $bill->customer->phone,
            'message' => $message,
            'type' => $type,
            'scheduled_at' => $scheduledAt,
        ]);
    }

    public static function createForCustomer(Customer $customer, string $type, string $message, ?Carbon $scheduledAt = null): self
    {
        return self::create([
            'customer_id' => $customer->id,
            'phone_number' => $customer->phone,
            'message' => $message,
            'type' => $type,
            'scheduled_at' => $scheduledAt,
        ]);
    }

    public static function getStatistics(): array
    {
        return [
            'total' => self::count(),
            'sent' => self::sent()->count(),
            'pending' => self::pending()->count(),
            'failed' => self::failed()->count(),
            'today_sent' => self::sent()->whereDate('sent_at', today())->count(),
            'this_month_sent' => self::sent()->whereMonth('sent_at', now()->month)->count(),
        ];
    }
}
