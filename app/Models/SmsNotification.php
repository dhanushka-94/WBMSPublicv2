<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class SmsNotification extends Model
{
    use HasFactory;

    /** SMS rows stay in the database this long. Older rows are removed; the monthly text files are kept. */
    public const DATABASE_RETENTION_DAYS = 90;

    /** A message is written to the monthly file once it reaches one of these statuses. */
    private const ARCHIVE_STATUSES = ['sent', 'failed', 'cancelled'];

    protected static function booted(): void
    {
        // Write the outcome of every send attempt: the first result, and each later resend result.
        static::created(function (SmsNotification $sms) {
            if (in_array($sms->status, self::ARCHIVE_STATUSES, true)) {
                $sms->appendToMonthlyFile();
            }
        });

        static::updated(function (SmsNotification $sms) {
            if (in_array($sms->status, self::ARCHIVE_STATUSES, true) && $sms->wasChanged(['status', 'retry_count', 'sent_at'])) {
                $sms->appendToMonthlyFile();
            }
        });
    }

    protected $fillable = [
        'customer_id',
        'bill_id',
        'payment_id',
        'receipt_number',
        'phone_number',
        'message',
        'segments',
        'cost_per_segment',
        'type',
        'status',
        'source',
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
        'http_status' => 'integer',
        'segments' => 'integer',
        'cost_per_segment' => 'decimal:4',
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

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
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

    /**
     * Readable reason for a failed SMS. Older rows saved "Unknown error occurred"
     * even though the provider's reply held the real reason.
     */
    public function getFailureReasonAttribute(): ?string
    {
        if ($this->status !== 'failed') {
            return null;
        }

        $reason = trim((string) $this->error_message);

        if (in_array($reason, ['', 'Unknown error occurred'], true) && is_array($this->response_data)) {
            $fromResponse = \App\Services\NotifyLkSmsService::extractError($this->response_data);
            if ($fromResponse !== 'Unknown error occurred') {
                $reason = $fromResponse;
            }
        }

        return $reason !== '' ? $reason : 'No reason returned by the SMS provider';
    }

    public function getFailureHintAttribute(): ?string
    {
        $reason = strtolower((string) $this->failure_reason);

        return match (true) {
            $reason === '' => null,
            str_contains($reason, 'balance') => 'Top up the Notify.lk balance, then resend.',
            str_contains($reason, 'invalid phone'), str_contains($reason, 'invalid number') => "Correct the customer's mobile number, then resend.",
            str_contains($reason, 'timed out'), str_contains($reason, 'curl error') => 'Network problem, safe to resend.',
            str_contains($reason, 'sender') => 'Check the Sender ID in SMS Settings.',
            str_contains($reason, 'api key'), str_contains($reason, 'user_id'), str_contains($reason, 'user id'), str_contains($reason, 'authenticat') => 'Check the User ID and API key in SMS Settings.',
            default => null,
        };
    }

    /**
     * Cost of this message. Only sent messages are charged.
     */
    public function getChargedAmountAttribute(): float
    {
        if ($this->status !== 'sent') {
            return 0.0;
        }

        $rate = $this->cost_per_segment !== null
            ? (float) $this->cost_per_segment
            : (float) config('sms.cost_per_segment', 0);

        return max(1, (int) $this->segments) * $rate;
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
            'custom' => 'Custom SMS',
            'reconnection' => 'Reconnection Notice',
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

    /**
     * Extra customer-less rows that older versions wrote for every SMS sent: no customer, bill or
     * payment, and a real row with the same phone number and message exists.
     */
    public function scopeLegacyDuplicates($query)
    {
        return $query->whereNull('customer_id')
            ->whereNull('bill_id')
            ->whereNull('payment_id')
            ->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('sms_notifications as orig')
                    ->whereColumn('orig.phone_number', 'sms_notifications.phone_number')
                    ->whereColumn('orig.message', 'sms_notifications.message')
                    ->whereColumn('orig.id', '!=', 'sms_notifications.id')
                    ->whereNotNull('orig.customer_id');
            });
    }

    /**
     * Copy rows that existed before monthly files started, then remove database rows older than
     * 90 days. Runs at most once a day. The text files are kept.
     */
    public static function maintainArchive(): void
    {
        $dir = storage_path('logs/sms');
        $marker = $dir.DIRECTORY_SEPARATOR.'.backfilled';
        $stamp = storage_path('framework/sms-prune-stamp.txt');
        $today = now()->toDateString();
        $needsBackfill = !is_file($marker);
        $needsPrune = !is_file($stamp) || trim((string) file_get_contents($stamp)) !== $today;

        if (!$needsBackfill && !$needsPrune) {
            return;
        }

        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            return;
        }

        if (!Schema::hasTable('sms_notifications')) {
            return;
        }

        $lock = fopen($dir.DIRECTORY_SEPARATOR.'.lock', 'c');
        if ($lock === false || !flock($lock, LOCK_EX)) {
            if (is_resource($lock)) {
                fclose($lock);
            }
            return;
        }

        try {
            if (!is_file($marker)) {
                $progress = $dir.DIRECTORY_SEPARATOR.'.backfill-id';
                $lastId = is_file($progress) ? (int) trim((string) file_get_contents($progress)) : 0;

                self::with('customer')->where('id', '>', $lastId)->orderBy('id')->chunkById(200, function ($rows) use ($progress) {
                    // The old double-logging rows are skipped so they do not reach the files.
                    $duplicates = self::legacyDuplicates()->whereIn('id', $rows->pluck('id'))->pluck('id')->flip();

                    foreach ($rows as $row) {
                        if (!isset($duplicates[$row->id])) {
                            $row->appendToMonthlyFile($row->sent_at ?? $row->updated_at ?? $row->created_at);
                        }
                        file_put_contents($progress, (string) $row->id);
                    }
                });

                file_put_contents($marker, now()->toIso8601String());
            }

            if (!is_file($stamp) || trim((string) file_get_contents($stamp)) !== $today) {
                self::with('customer')
                    ->where('created_at', '<', now()->subDays(self::DATABASE_RETENTION_DAYS))
                    // A message still waiting for its scheduled time is not old yet.
                    ->where(function ($q) {
                        $q->whereNull('scheduled_at')->orWhere('scheduled_at', '<', now())->orWhere('status', '!=', 'pending');
                    })
                    ->chunkById(500, function ($rows) {
                        foreach ($rows as $row) {
                            // Messages that never reached a final status are written now, so nothing is lost.
                            if (!in_array($row->status, self::ARCHIVE_STATUSES, true)) {
                                $row->appendToMonthlyFile($row->updated_at ?? $row->created_at);
                            }
                        }
                        self::whereIn('id', $rows->pluck('id'))->delete();
                    });

                file_put_contents($stamp, $today);
            }
        } catch (\Throwable $e) {
            report($e);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /**
     * Add one line to this month's text file: storage/logs/sms/YYYY-MM.txt
     */
    public function appendToMonthlyFile(?Carbon $at = null): void
    {
        try {
            $at = $at ? Carbon::instance($at) : now();
            $dir = storage_path('logs/sms');
            if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
                return;
            }

            $path = $dir.DIRECTORY_SEPARATOR.$at->format('Y-m').'.txt';
            $customer = $this->customer;
            $line = implode(' | ', [
                $at->format('Y-m-d H:i:s'),
                'sms#'.$this->id,
                $this->archiveText($this->phone_number),
                $this->archiveText($customer?->full_name),
                $this->archiveText($customer?->account_number),
                $this->archiveText($this->type),
                $this->archiveText($this->source),
                $this->archiveText($this->status),
                'segments='.max(1, (int) $this->segments),
                'cost='.number_format($this->charged_amount, 2, '.', ''),
                $this->archiveText($this->status === 'failed' ? $this->failure_reason : null),
                $this->archiveText($this->message),
            ]).PHP_EOL;

            $handle = fopen($path, 'ab');
            if ($handle === false) {
                return;
            }

            try {
                if (!flock($handle, LOCK_EX)) {
                    return;
                }
                clearstatcache(true, $path);
                if (!is_file($path) || filesize($path) === 0) {
                    fwrite($handle, '# AquaBill SMS log '.$at->format('Y-m').PHP_EOL);
                    fwrite($handle, '# date time | sms id | phone | customer | account | type | source | status | segments | cost (Rs., sent only) | failure reason | message'.PHP_EOL);
                }
                fwrite($handle, $line);
                fflush($handle);
                flock($handle, LOCK_UN);
            } finally {
                fclose($handle);
            }
        } catch (\Throwable $e) {
            // Never let a file problem stop an SMS from being sent or recorded.
            report($e);
        }
    }

    public static function archiveMonths(): array
    {
        $months = [];
        foreach (glob(storage_path('logs/sms/*.txt')) ?: [] as $file) {
            $name = basename($file, '.txt');
            if (preg_match('/^\d{4}-\d{2}$/', $name)) {
                $months[] = $name;
            }
        }
        rsort($months);

        return $months;
    }

    public static function monthlyArchivePath(string $month): ?string
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            return null;
        }

        return storage_path('logs/sms/'.$month.'.txt');
    }

    private function archiveText(?string $value): string
    {
        $value = preg_replace('/\s+/', ' ', trim((string) $value)) ?? '';
        // A "|" inside a value would shift the columns when the file is opened in a spreadsheet.
        $value = str_replace('|', '/', $value);

        return $value === '' ? '-' : $value;
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
