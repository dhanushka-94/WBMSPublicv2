<?php

namespace App\Helpers;

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
        
        return $result;
    }

    /**
     * Disable the system
     */
    public static function disableSystem(string $reason = 'System disabled by administrator'): bool
    {
        $result = self::set('system_enabled', false, 'boolean');
        self::set('system_disabled_at', now()->toDateTimeString(), 'datetime');
        self::set('disable_reason', $reason, 'string');
        
        return $result;
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