<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletEntry extends Model
{
    protected $fillable = [
        'customer_id',
        'bill_id',
        'payment_id',
        'user_id',
        'amount',
        'reason',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

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

    /**
     * Overpayments still tied to a living payment (excludes reversed/orphan rows).
     */
    public function scopeLivingOverpayments($query)
    {
        return $query->where('reason', 'overpayment')
            ->where('amount', '>', 0)
            ->whereNotNull('payment_id')
            ->whereExists(function ($exists) {
                $exists->selectRaw('1')
                    ->from('payments')
                    ->whereColumn('payments.id', 'wallet_entries.payment_id');
            });
    }
}
