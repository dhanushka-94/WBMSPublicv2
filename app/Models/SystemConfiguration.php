<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get a configuration value by key
     */
    public static function get(string $key, $default = null)
    {
        $config = Cache::remember("system_config_{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (!$config) {
            return $default;
        }

        return static::castValue($config->value, $config->type);
    }

    /**
     * Set a configuration value
     */
    public static function set(string $key, $value, string $type = 'string', string $description = null): void
    {
        $config = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description
            ]
        );

        Cache::forget("system_config_{$key}");
    }

    /**
     * Cast value based on type
     */
    protected static function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true),
            'array' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Get default billing day for new customers
     */
    public static function getDefaultBillingDay(): int
    {
        return static::get('default_billing_day', 1);
    }

    /**
     * Set default billing day
     */
    public static function setDefaultBillingDay(int $day): void
    {
        static::set('default_billing_day', $day, 'integer', 'Default billing day of the month (1-31) for new customers');
    }

    /**
     * Get default auto-billing status
     */
    public static function getDefaultAutoBilling(): bool
    {
        return static::get('auto_billing_enabled_default', true);
    }

    /**
     * Set default auto-billing status
     */
    public static function setDefaultAutoBilling(bool $enabled): void
    {
        static::set('auto_billing_enabled_default', $enabled ? '1' : '0', 'boolean', 'Default auto-billing status for new customers');
    }

    /**
     * Get billing cycle type
     */
    public static function getBillingCycleType(): string
    {
        return static::get('billing_cycle_type', 'monthly');
    }

    /**
     * Set billing cycle type
     */
    public static function setBillingCycleType(string $type): void
    {
        static::set('billing_cycle_type', $type, 'string', 'Billing cycle frequency (monthly, quarterly, etc.)');
    }
}
