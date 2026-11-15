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

    public static function oneReadingPerMonth(): bool
    {
        $config = Cache::remember('system_config_one_reading_per_month', 3600, function () {
            return static::where('key', 'one_reading_per_month')->first();
        });

        if (!$config) {
            return false;
        }

        return filter_var($config->value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function mobileAppMaintenance(): bool
    {
        $config = Cache::remember('system_config_mobile_app_maintenance', 3600, function () {
            return static::where('key', 'mobile_app_maintenance')->first();
        });

        if (!$config) {
            return false;
        }

        return filter_var($config->value, FILTER_VALIDATE_BOOLEAN);
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

    public static function googleMapsKey(): string
    {
        $saved = trim((string) static::get('google_maps_api_key', ''));

        if ($saved !== '') {
            return $saved;
        }

        return trim((string) config('services.google_maps.key', ''));
    }

    /**
     * Reader mobile APK release metadata stored in system_configurations.
     *
     * @return array{version:?string,path:?string,original_name:?string,file_size:?int,uploaded_at:?string}
     */
    public static function mobileAppRelease(): array
    {
        $size = static::get('mobile_app_file_size', null);

        return [
            'version' => static::get('mobile_app_version') ?: null,
            'path' => static::get('mobile_app_path') ?: null,
            'original_name' => static::get('mobile_app_original_name') ?: null,
            'file_size' => $size !== null && $size !== '' ? (int) $size : null,
            'uploaded_at' => static::get('mobile_app_uploaded_at') ?: null,
        ];
    }

    public static function setMobileAppRelease(
        string $version,
        string $path,
        string $originalName,
        int $fileSize,
        ?string $uploadedAt = null
    ): void {
        $uploadedAt = $uploadedAt ?: now()->toDateTimeString();

        static::set('mobile_app_version', $version, 'string', 'Reader mobile app version');
        static::set('mobile_app_path', $path, 'string', 'Reader mobile APK storage path');
        static::set('mobile_app_original_name', $originalName, 'string', 'Original uploaded APK filename');
        static::set('mobile_app_file_size', (string) $fileSize, 'integer', 'Reader mobile APK file size in bytes');
        static::set('mobile_app_uploaded_at', $uploadedAt, 'string', 'When the reader mobile APK was uploaded');
    }
}
