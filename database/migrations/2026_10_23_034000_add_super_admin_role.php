<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin', 'admin', 'staff', 'manager', 'meter_reader', 'billing_officer', 'cashier') NOT NULL DEFAULT 'staff'");
        DB::table('users')->where('id', 1)->update(['role' => 'super_admin']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'super_admin')->update(['role' => 'admin']);
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'staff', 'manager', 'meter_reader', 'billing_officer', 'cashier') NOT NULL DEFAULT 'staff'");
    }
};
