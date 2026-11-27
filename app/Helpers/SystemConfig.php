<?php

namespace App\Helpers;

use App\Models\SystemConfiguration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SystemConfig
{
    /**
     * Get system configuration value
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = "system_config_{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $config = DB::table('system_configurations')
                ->where('key', $key)
                ->first();

            if (!$config) {
                return $default;
            }

            // Type casting based on the type field
            return match($config->type) {
                'boolean' => (bool) $config->value,
                'integer' => (int) $config->value,
                'json' => json_decode($config->value, true),
                default => $config->value,
            };
        });
    }

    /**
     * Set system configuration value
     */
    public static function set(string $key, $value, string $type = 'string'): bool
    {
        $cacheKey = "system_config_{$key}";
        Cache::forget($cacheKey);

        // Convert value based on type
        $dbValue = match($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value,
        };

        $result = DB::table('system_configurations')
            ->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $dbValue,
                    'type' => $type,
                    'updated_at' => now(),
                ]
            );

        return $result;
    }

    /**
     * Check if system is enabled
     */
    public static function isSystemEnabled(): bool
    {
        return self::get('system_enabled', true);
    }

    /**
     * Enable the system
     */
    public static function enableSystem(): bool
    {
        $result = self::set('system_enabled', true, 'boolean');
        self::set('system_disabled_at', null, 'datetime');
        self::set('disable_reason', null, 'string');

        // Put the reader app back the way it was before the system was disabled.
        $before = self::get('reader_app_maintenance_before_disable');
        if ($before === '0' || $before === '1') {
            self::set('mobile_app_maintenance', $before === '1', 'boolean');
        }
        self::set('reader_app_maintenance_before_disable', '', 'string');

        return $result;
    }

    /**
     * Disable the system. The meter reader app goes into maintenance too, and stays there
     * until the system is enabled again.
     */
    public static function disableSystem(string $reason = 'System disabled by administrator'): bool
    {
        // Only the first disable remembers the reader app's state, so a second disable can't overwrite it.
        if (self::isSystemEnabled()) {
            self::set('reader_app_maintenance_before_disable', self::readerAppInMaintenance() ? '1' : '0', 'string');
        }

        $result = self::set('system_enabled', false, 'boolean');
        self::set('system_disabled_at', now()->toDateTimeString(), 'datetime');
        self::set('disable_reason', $reason, 'string');
        self::set('mobile_app_maintenance', true, 'boolean');

        return $result;
    }

    /**
     * Read the reader app switch the same way the API does. Don't read this key through get():
     * SystemConfiguration caches the same key as a model, so the two readers would clash.
     */
    public static function readerAppInMaintenance(): bool
    {
        return SystemConfiguration::mobileAppMaintenance();
    }

    /**
     * Get system disable information
     */
    public static function getDisableInfo(): array
    {
        return [
            'disabled_at' => self::get('system_disabled_at'),
            'reason' => self::get('disable_reason', 'System disabled by administrator'),
        ];
    }

    /**
     * Clear configuration cache
     */
    public static function clearCache(): void
    {
        $keys = DB::table('system_configurations')->pluck('key');
        
        foreach ($keys as $key) {
            Cache::forget("system_config_{$key}");
        }
    }
}