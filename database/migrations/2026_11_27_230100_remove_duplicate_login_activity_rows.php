<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sign-in, sign-out and failed-login rows were written twice for every event
     * (the listeners were registered twice). Keep the first row of each pair.
     */
    public function up(): void
    {
        if (!Schema::hasTable('activity_logs')) {
            return;
        }

        $seen = [];
        $delete = [];

        DB::table('activity_logs')
            ->whereIn('action', ['login', 'logout', 'login_failed'])
            ->orderBy('id')
            ->select(['id', 'user_id', 'user_email', 'action', 'description', 'ip_address', 'session_id', 'created_at'])
            ->chunkById(500, function ($rows) use (&$seen, &$delete) {
                foreach ($rows as $row) {
                    $key = implode('|', [$row->user_id, $row->user_email, $row->action, $row->description, $row->ip_address, $row->session_id, $row->created_at]);
                    if (isset($seen[$key])) {
                        $delete[] = $row->id;
                    } else {
                        $seen[$key] = true;
                    }
                }
            });

        foreach (array_chunk($delete, 500) as $ids) {
            DB::table('activity_logs')->whereIn('id', $ids)->delete();
        }
    }

    public function down(): void
    {
        // Removed duplicates cannot be restored.
    }
};
