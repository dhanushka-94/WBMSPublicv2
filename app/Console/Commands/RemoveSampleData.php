<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bill;
use App\Models\MeterReading;
use App\Models\WaterMeter;
use App\Models\Customer;
use App\Models\Guarantor;
use App\Models\Division;
use App\Models\CustomerType;
use Illuminate\Support\Facades\DB;

class RemoveSampleData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sample:clear {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all sample data except system users and rates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will remove ALL sample data including customers, meters, readings, and bills. System users and rate structure will be preserved. Are you sure?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info('Starting sample data removal...');
        $this->newLine();

        try {
            DB::beginTransaction();

            // Disable foreign key checks for MySQL
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Count existing records
            $billsCount = Bill::count();
            $readingsCount = MeterReading::count();
            $metersCount = WaterMeter::count();
            $customersCount = Customer::count();
            $guarantorsCount = Guarantor::count();
            $divisionsCount = Division::count();
            $customerTypesCount = CustomerType::count();

            $this->info("Current data counts:");
            $this->line("  Bills: {$billsCount}");
            $this->line("  Meter Readings: {$readingsCount}");
            $this->line("  Water Meters: {$metersCount}");
            $this->line("  Customers: {$customersCount}");
            $this->line("  Guarantors: {$guarantorsCount}");
            $this->line("  Divisions: {$divisionsCount}");
            $this->line("  Customer Types: {$customerTypesCount}");
            $this->newLine();

            // Remove data in correct order (respecting foreign key constraints)
            
            if ($billsCount > 0) {
                $this->info('🗑️  Removing bills...');
                Bill::truncate();
                $this->line("  ✅ Removed {$billsCount} bills");
            }

            if ($readingsCount > 0) {
                $this->info('🗑️  Removing meter readings...');
                MeterReading::truncate();
                $this->line("  ✅ Removed {$readingsCount} meter readings");
            }

            if ($metersCount > 0) {
                $this->info('🗑️  Removing water meters...');
                WaterMeter::truncate();
                $this->line("  ✅ Removed {$metersCount} water meters");
            }

            if ($customersCount > 0) {
                $this->info('🗑️  Removing customers...');
                Customer::truncate();
                $this->line("  ✅ Removed {$customersCount} customers");
            }

            if ($guarantorsCount > 0) {
                $this->info('🗑️  Removing guarantors...');
                Guarantor::truncate();
                $this->line("  ✅ Removed {$guarantorsCount} guarantors");
            }

            // Ask about divisions and customer types
            if ($divisionsCount > 0 && (!$this->option('force') && $this->confirm('Remove divisions? (These might be system data you want to keep)'))) {
                Division::truncate();
                $this->line("  ✅ Removed {$divisionsCount} divisions");
            }

            if ($customerTypesCount > 0 && (!$this->option('force') && $this->confirm('Remove customer types? (These might be system data you want to keep)'))) {
                CustomerType::truncate();
                $this->line("  ✅ Removed {$customerTypesCount} customer types");
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            // Reset auto-increment counters for MySQL
            $this->info('🔄 Resetting auto-increment counters...');
            
            $tables = ['bills', 'meter_readings', 'water_meters', 'customers', 'guarantors'];
            foreach ($tables as $table) {
                DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
            }

            DB::commit();

            $this->newLine();
            $this->info('✅ Sample data removal completed successfully!');
            $this->newLine();
            $this->info('Preserved data:');
            $this->line('  - System users (admin, staff, manager, meter reader)');
            $this->line('  - Rate structure and billing configuration');
            $this->line('  - Application settings and configuration');
            
            $this->newLine();
            $this->warn('💡 The system is now clean and ready for production data.');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error removing sample data: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
