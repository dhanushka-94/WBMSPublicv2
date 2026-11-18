<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'bill_id',
        'customer_id',
        'received_by',
        'receipt_number',
        'amount',
        'payment_method',
        'paid_on',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_on' => 'datetime',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function walletEntries(): HasMany
    {
        return $this->hasMany(WalletEntry::class);
    }

    public function overpaymentEntries(): HasMany
    {
        return $this->hasMany(WalletEntry::class)
            ->where('reason', 'overpayment')
            ->where('amount', '>', 0);
    }

    /**
     * Extra cash from this payment that was credited to the customer wallet.
     */
    public function walletAddedAmount(): float
    {
        if ($this->payment_method === 'wallet') {
            return 0.0;
        }

        $linked = (float) ($this->relationLoaded('overpaymentEntries')
            ? $this->overpaymentEntries->sum('amount')
            : $this->overpaymentEntries()->sum('amount'));

        if ($linked > 0) {
            return round($linked, 2);
        }

        if (!$this->customer_id || !$this->created_at) {
            return 0.0;
        }

        return round((float) WalletEntry::query()
            ->where('customer_id', $this->customer_id)
            ->where('reason', 'overpayment')
            ->where('amount', '>', 0)
            ->where(function ($query) {
                $query->whereNull('payment_id')->orWhere('payment_id', $this->id);
            })
            ->whereBetween('created_at', [
                $this->created_at->copy()->subSeconds(2),
                $this->created_at->copy()->addSeconds(2),
            ])
            ->sum('amount'), 2);
    }

    /**
     * Exact cash/payment received for this receipt (bill portion + wallet overpayment).
     */
    public function exactPaidAmount(): float
    {
        return round((float) $this->amount + $this->walletAddedAmount(), 2);
    }

    public function paidOnDisplay(string $format = 'Y-m-d H:i'): string
    {
        return optional($this->paid_on)->timezone(config('app.timezone'))->format($format) ?? '—';
    }

    public function getLogName(): string
    {
        return $this->receipt_number ?: 'Payment #'.$this->id;
    }

    /**
     * Accept date, datetime-local, or full datetime and keep a real timestamp.
     */
    public static function parsePaidOn(mixed $value): Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy()->timezone(config('app.timezone'));
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return now();
        }

        $parsed = Carbon::parse(str_replace('T', ' ', $raw), config('app.timezone'));

        // Date-only values (no time) get the current clock so new payments are stamped.
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            if ($parsed->isSameDay(now())) {
                return now();
            }

            return $parsed->startOfDay();
        }

        return $parsed;
    }

    public static function nextReceiptNumber(): string
    {
        $prefix = 'RC'.now()->format('Ym');
        $last = static::where('receipt_number', 'like', $prefix.'%')->orderByDesc('receipt_number')->value('receipt_number');
        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
