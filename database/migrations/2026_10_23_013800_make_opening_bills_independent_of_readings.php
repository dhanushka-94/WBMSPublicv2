<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Opening bills are typed amounts, so they do not need a meter reading row.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE bills DROP FOREIGN KEY bills_meter_reading_id_foreign');
        DB::statement('ALTER TABLE bills MODIFY meter_reading_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE bills ADD CONSTRAINT bills_meter_reading_id_foreign FOREIGN KEY (meter_reading_id) REFERENCES meter_readings(id) ON DELETE SET NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE bills DROP FOREIGN KEY bills_meter_reading_id_foreign');
        DB::statement('ALTER TABLE bills MODIFY meter_reading_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE bills ADD CONSTRAINT bills_meter_reading_id_foreign FOREIGN KEY (meter_reading_id) REFERENCES meter_readings(id) ON DELETE CASCADE');
    }
};
