<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    protected $fillable = [
        'key',
        'name',
        'body',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function bodyFor(string $key, string $fallback): string
    {
        $template = static::where('key', $key)->first();

        return $template?->body ?: $fallback;
    }

    public static function allows(string $key): bool
    {
        $template = static::where('key', $key)->first();

        return $template ? $template->is_active : true;
    }
}
