<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reader_route_divisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('division_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'division_id']);
        });

        Schema::create('reader_route_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reader_route_customers');
        Schema::dropIfExists('reader_route_divisions');
    }
};
