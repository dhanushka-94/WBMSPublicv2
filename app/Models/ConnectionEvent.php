<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConnectionEvent extends Model
{
    protected $fillable = [
        'customer_id',
        'user_id',
        'action',
        'event_date',
        'reason',
        'notes',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
