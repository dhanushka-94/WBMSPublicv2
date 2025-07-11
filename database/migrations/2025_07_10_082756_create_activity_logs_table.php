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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            
            // User information
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('user_name')->nullable(); // Store name even if user is deleted
            $table->string('user_email')->nullable(); // Store email even if user is deleted
            $table->string('user_role')->nullable(); // Store role at time of action
            
            // Activity details
            $table->string('action'); // create, read, update, delete, login, logout, etc.
            $table->string('subject_type')->nullable(); // Model class name (Customer, WaterMeter, etc.)
            $table->unsignedBigInteger('subject_id')->nullable(); // Model ID
            $table->string('subject_name')->nullable(); // Human readable subject name
            
            // Action context
            $table->string('description'); // Human readable description
            $table->string('module'); // Module/section (customers, meters, readings, bills, auth, etc.)
            $table->string('method')->nullable(); // HTTP method (GET, POST, PUT, DELETE)
            $table->string('url')->nullable(); // Request URL
            $table->string('route_name')->nullable(); // Laravel route name
            
            // Data tracking
            $table->json('old_values')->nullable(); // Previous values for updates
            $table->json('new_values')->nullable(); // New values for creates/updates
            $table->json('properties')->nullable(); // Additional data/context
            
            // Request information
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            
            // Status and metadata
            $table->enum('status', ['success', 'failed', 'warning'])->default('success');
            $table->text('error_message')->nullable(); // If action failed
            $table->integer('duration_ms')->nullable(); // Action duration in milliseconds
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['module', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->index(['ip_address', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
