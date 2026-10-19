<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tariff rows are entered in the office. A fresh database starts with an empty rates table.
    }

    public function down(): void
    {
        DB::table('rates')->delete();
    }
};
