<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sms_notifications', 'cost_per_segment')) {
            Schema::table('sms_notifications', function (Blueprint $table) {
                $table->decimal('cost_per_segment', 8, 4)->nullable()->after('segments');
            });
        }

        // Lock existing messages to the rate in .env at the time of this migration.
        $rate = (float) config('sms.cost_per_segment', 0);
        if ($rate > 0) {
            DB::table('sms_notifications')->whereNull('cost_per_segment')->update(['cost_per_segment' => $rate]);
        }
    }

    public function down(): void
    {
        Schema::table('sms_notifications', function (Blueprint $table) {
            $table->dropColumn('cost_per_segment');
        });
    }
};
