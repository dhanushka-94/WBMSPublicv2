<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sms_rate_changes')) {
            Schema::create('sms_rate_changes', function (Blueprint $table) {
                $table->id();
                $table->decimal('rate', 8, 4);
                $table->decimal('previous_rate', 8, 4)->nullable();
                $table->unsignedBigInteger('changed_by')->nullable();
                $table->string('changed_by_name')->nullable();
                $table->string('note', 255)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // The first row is the rate the system was using when this feature arrived (SMS_COST_PER_SEGMENT).
        if (DB::table('sms_rate_changes')->count() === 0) {
            DB::table('sms_rate_changes')->insert([
                'rate' => round((float) config('sms.cost_per_segment', 0), 4),
                'previous_rate' => null,
                'changed_by' => null,
                'changed_by_name' => 'System',
                'note' => 'Starting rate (taken from the server .env file)',
                'created_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_rate_changes');
    }
};
