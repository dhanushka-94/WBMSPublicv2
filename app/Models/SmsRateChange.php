<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * History of the SMS price per segment. The newest row is the rate in use.
 * Only the Super Admin can add rows (SMS Settings page).
 */
class SmsRateChange extends Model
{
    public const CACHE_KEY = 'sms_cost_per_segment_rate';

    public const UPDATED_AT = null;

    protected $fillable = ['rate', 'previous_rate', 'changed_by', 'changed_by_name', 'note'];

    protected $casts = [
        'rate' => 'float',
        'previous_rate' => 'float',
        'created_at' => 'datetime',
    ];

    /**
     * The rate in use. Falls back to SMS_COST_PER_SEGMENT in .env until a rate is saved here.
     */
    public static function currentRate(): float
    {
        $cached = Cache::get(self::CACHE_KEY);
        if ($cached !== null) {
            return (float) $cached;
        }

        $latest = static::query()->orderByDesc('id')->value('rate');
        if ($latest === null) {
            return (float) config('sms.cost_per_segment', 0);
        }

        Cache::forever(self::CACHE_KEY, (float) $latest);

        return (float) $latest;
    }

    /**
     * Make config('sms.cost_per_segment') follow the saved rate, so every place that prices an SMS agrees.
     */
    public static function applyToConfig(): void
    {
        try {
            config(['sms.cost_per_segment' => static::currentRate()]);
        } catch (\Throwable $e) {
            // The table does not exist yet (fresh install or mid-migration): keep the .env value.
        }
    }

    /**
     * Save a new rate and add it to the history. Returns null when the rate did not change.
     */
    public static function change(float $rate, ?User $user, ?string $note = null): ?self
    {
        $rate = round($rate, 4);
        $previous = round(static::currentRate(), 4);

        if (abs($rate - $previous) < 0.00005) {
            return null;
        }

        $row = static::create([
            'rate' => $rate,
            'previous_rate' => $previous,
            'changed_by' => $user?->id,
            'changed_by_name' => $user?->name,
            'note' => $note !== null && trim($note) !== '' ? trim($note) : null,
        ]);

        Cache::forever(self::CACHE_KEY, $rate);
        config(['sms.cost_per_segment' => $rate]);

        return $row;
    }
}
