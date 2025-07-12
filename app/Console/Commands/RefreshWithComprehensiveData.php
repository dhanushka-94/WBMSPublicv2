<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefreshWithComprehensiveData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:refresh-comprehensive 
                            {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all data and populate with comprehensive test data (100+ records each)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Water Billing Management System - Comprehensive Data Refresh');
        $this->line('');
        
        if (!$this->option('force')) {
            $this->warn('⚠️  This will PERMANENTLY DELETE all existing data!');
            $this->line('');
            $this->info('📊 This will populate the system with:');
            $this->line('   • 100+ Users (admin, meter readers, staff, managers)');
            $this->line('   • 120+ Divisions');
            $this->line('   • 15+ Customer Types');
            $this->line('   • 150+ Guarantors');
            $this->line('   • 100+ Rate structures');
            $this->line('   • 150+ Customers');
            $this->line('   • 150+ Water Meters');
            $this->line('   • 500+ Meter Readings');
            $this->line('   • 200+ Bills');
            $this->line('');
            
            if (!$this->confirm('Are you sure you want to proceed?')) {
                $this->error('Operation cancelled.');
                return;
            }
        }
        
        $this->info('🗑️  Clearing existing data...');
        
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        
        // Get all table names
        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();
        
        foreach ($tables as $table) {
            $tableName = $table->{"Tables_in_{$dbName}"};
            
            // Skip migration tables
            if (in_array($tableName, ['migrations', 'failed_jobs', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches'])) {
                continue;
            }
            
            $this->line("   🗑️  Truncating {$tableName}...");
            DB::table($tableName)->truncate();
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        
        $this->info('✅ All data cleared successfully!');
        $this->line('');
        
        $this->info('🌱 Seeding comprehensive data...');
        
        // Run the comprehensive seeder
        $this->call('db:seed', ['--class' => 'ComprehensiveSeeder']);
        
        $this->line('');
        $this->info('🎉 Comprehensive data refresh completed!');
        $this->line('');
        
        // Display summary
        $this->info('📊 Data Summary:');
        $this->displayDataSummary();
        
        $this->line('');
        $this->info('🔑 Login Information:');
        $this->line('   Email: admin@wassip.com');
        $this->line('   Password: password');
        $this->line('');
        $this->info('💡 Test Accounts:');
        $this->line('   Meter Readers: reader1@wassip.com to reader50@wassip.com');
        $this->line('   Staff Members: staff1@wassip.com to staff30@wassip.com');
        $this->line('   Managers: manager1@wassip.com to manager20@wassip.com');
        $this->line('   (All passwords: password)');
        $this->line('');
        $this->info('🌐 Access: http://127.0.0.1:8000');
    }
    
    private function displayDataSummary(): void
    {
        $tables = [
            'users' => 'Users',
            'divisions' => 'Divisions',
            'customer_types' => 'Customer Types',
            'guarantors' => 'Guarantors',
            'rates' => 'Rates',
            'customers' => 'Customers',
            'water_meters' => 'Water Meters',
            'meter_readings' => 'Meter Readings',
            'bills' => 'Bills',
        ];
        
        foreach ($tables as $table => $label) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $this->line("   ✅ {$label}: {$count}");
            }
        }
    }
} 