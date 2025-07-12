<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->string('meter_condition')->nullable()->after('notes'); // good, damaged, broken, needs_repair
            $table->string('photo_path')->nullable()->after('meter_condition');
            $table->decimal('gps_latitude', 10, 8)->nullable()->after('photo_path');
            $table->decimal('gps_longitude', 11, 8)->nullable()->after('gps_latitude');
            $table->string('submitted_via')->nullable()->after('gps_longitude'); // mobile_app, web, manual
            $table->timestamp('offline_timestamp')->nullable()->after('submitted_via');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->dropColumn([
                'meter_condition',
                'photo_path',
                'gps_latitude',
                'gps_longitude',
                'submitted_via',
                'offline_timestamp'
            ]);
        });
    }
};
