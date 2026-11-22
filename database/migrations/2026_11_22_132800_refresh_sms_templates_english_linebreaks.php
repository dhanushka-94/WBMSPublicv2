<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $templates = config('sms.templates', []);

        foreach ($templates as $key => $body) {
            $existing = DB::table('sms_templates')->where('key', $key)->first();

            if ($existing) {
                DB::table('sms_templates')->where('key', $key)->update([
                    'body' => $body,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('sms_templates')->insert([
                    'key' => $key,
                    'name' => str_replace('_', ' ', ucwords($key, '_')),
                    'body' => $body,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Intentional no-op: template wording is content, not schema.
    }
};
