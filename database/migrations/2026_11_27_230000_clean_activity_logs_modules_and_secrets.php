<?php

use App\Models\ActivityLog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SENSITIVE = '/pass(word)?|token|secret|api[_-]?key|(^|_)key$|authorization|credential|\botp\b|(^|_)pin$/i';

    public function up(): void
    {
        if (!Schema::hasTable('activity_logs')) {
            return;
        }

        // 1. One spelling per module ("customer" -> "customers", "login" -> "authentication", ...).
        foreach (ActivityLog::MODULE_ALIASES as $canonical => $aliases) {
            DB::table('activity_logs')->whereIn('module', $aliases)->update(['module' => $canonical]);
        }

        // 2. Earlier versions saved API keys and similar secrets in plain text inside "properties".
        DB::table('activity_logs')
            ->where(function ($q) {
                foreach (['api_key', 'api-key', 'apikey', 'secret', 'token', 'password', 'authorization', 'credential', 'otp', 'pin'] as $word) {
                    $q->orWhere('properties', 'like', '%'.$word.'%');
                }
            })
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $decoded = json_decode((string) $row->properties, true);
                    if (!is_array($decoded)) {
                        continue;
                    }

                    $masked = $this->mask($decoded);
                    if ($masked !== $decoded) {
                        DB::table('activity_logs')->where('id', $row->id)->update([
                            'properties' => json_encode($masked, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        ]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Merged module names and masked secrets cannot be restored.
    }

    private function mask(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->mask($value);
            } elseif (is_string($key) && is_string($value) && $value !== '[hidden]' && preg_match(self::SENSITIVE, $key)) {
                $data[$key] = '[hidden]';
            }
        }

        return $data;
    }
};
