<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE customers MODIFY connection_date DATE NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE customers SET connection_date = CURDATE() WHERE connection_date IS NULL");
        DB::statement('ALTER TABLE customers MODIFY connection_date DATE NOT NULL');
    }
};
