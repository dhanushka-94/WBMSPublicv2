<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('qr_code_path')->nullable()->after('meter_number');
        });

        $meters = DB::table('water_meters')
            ->whereNotNull('customer_id')
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->get(['customer_id', 'meter_number']);

        $linked = [];
        foreach ($meters as $meter) {
            if (isset($linked[$meter->customer_id])) {
                continue;
            }
            $linked[$meter->customer_id] = $meter->meter_number;
        }

        foreach (array_chunk($linked, 200, true) as $chunk) {
            foreach ($chunk as $customerId => $meterNumber) {
                DB::table('customers')->where('id', $customerId)->update([
                    'meter_number' => $meterNumber,
                    'qr_code_path' => "qr-codes/meters/meter_{$meterNumber}_200.svg",
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('qr_code_path');
        });
    }
};
