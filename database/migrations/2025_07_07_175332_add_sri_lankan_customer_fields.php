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
        // First update guarantors table
        Schema::table('guarantors', function (Blueprint $table) {
            $table->string('guarantor_id')->unique()->after('id');
            $table->string('first_name')->after('guarantor_id');
            $table->string('last_name')->after('first_name');
            $table->string('nic')->unique()->after('last_name');
            $table->string('phone')->after('nic');
            $table->string('email')->nullable()->after('phone');
            $table->text('address')->after('email');
            $table->string('relationship')->after('address'); // Father, Mother, Spouse, etc.
            $table->boolean('is_active')->default(true)->after('relationship');
        });

        // Then update customers table with Sri Lankan fields
        Schema::table('customers', function (Blueprint $table) {
            $table->string('reference_number')->unique()->nullable()->after('account_number');
            $table->string('meter_number')->unique()->nullable()->after('reference_number');
            $table->string('phone_two')->nullable()->after('phone');
            $table->string('nic', 12)->unique()->nullable()->after('email'); // Sri Lankan NIC
            $table->string('epf_number')->unique()->nullable()->after('nic'); // EPF Number
            $table->foreignId('guarantor_id')->nullable()->after('staff_type')->constrained('guarantors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['guarantor_id']);
            $table->dropColumn(['reference_number', 'meter_number', 'phone_two', 'nic', 'epf_number', 'guarantor_id']);
        });

        Schema::table('guarantors', function (Blueprint $table) {
            $table->dropColumn(['guarantor_id', 'first_name', 'last_name', 'nic', 'phone', 'email', 'address', 'relationship', 'is_active']);
        });
    }
};
