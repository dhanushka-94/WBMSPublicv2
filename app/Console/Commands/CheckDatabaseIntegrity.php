<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WaterMeter;
use App\Models\Customer;
use App\Models\MeterReading;
use App\Models\Bill;
use DB;

class CheckDatabaseIntegrity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:check-integrity {--fix : Automatically fix found issues}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check database integrity and optionally fix issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking Database Integrity...');
        $this->newLine();

        $issues = 0;
        $fix = $this->option('fix');

        // Check 1: Orphaned water meters
        $this->info('1. Checking for orphaned water meters...');
        $orphanedMeters = WaterMeter::whereNull('customer_id')
            ->orWhereNotExists(function($query) {
                $query->select(DB::raw(1))
                       ->from('customers')
                       ->whereRaw('customers.id = water_meters.customer_id');
            })->get();

        if ($orphanedMeters->count() > 0) {
            $issues++;
            $this->warn("   ⚠️  Found {$orphanedMeters->count()} orphaned meters");
            
            if ($fix) {
                foreach ($orphanedMeters as $meter) {
                    $this->line("   🗑️  Deleting meter: {$meter->meter_number}");
                    $meter->meterReadings()->delete();
                    $meter->delete();
                }
                $this->info("   ✅ Fixed orphaned meters");
            } else {
                $this->line("   💡 Run with --fix to automatically remove orphaned meters");
            }
        } else {
            $this->info("   ✅ No orphaned meters found");
        }

        // Check 2: Orphaned meter readings
        $this->info('2. Checking for orphaned meter readings...');
        $orphanedReadings = MeterReading::whereNotExists(function($query) {
            $query->select(DB::raw(1))
                   ->from('water_meters')
                   ->whereRaw('water_meters.id = meter_readings.water_meter_id');
        })->get();

        if ($orphanedReadings->count() > 0) {
            $issues++;
            $this->warn("   ⚠️  Found {$orphanedReadings->count()} orphaned meter readings");
            
            if ($fix) {
                $orphanedReadings->each->delete();
                $this->info("   ✅ Deleted orphaned meter readings");
            } else {
                $this->line("   💡 Run with --fix to automatically remove orphaned readings");
            }
        } else {
            $this->info("   ✅ No orphaned meter readings found");
        }

        // Check 3: Orphaned bills
        $this->info('3. Checking for orphaned bills...');
        $orphanedBills = Bill::whereNotExists(function($query) {
            $query->select(DB::raw(1))
                   ->from('customers')
                   ->whereRaw('customers.id = bills.customer_id');
        })->get();

        if ($orphanedBills->count() > 0) {
            $issues++;
            $this->warn("   ⚠️  Found {$orphanedBills->count()} orphaned bills");
            
            if ($fix) {
                $orphanedBills->each->delete();
                $this->info("   ✅ Deleted orphaned bills");
            } else {
                $this->line("   💡 Run with --fix to automatically remove orphaned bills");
            }
        } else {
            $this->info("   ✅ No orphaned bills found");
        }

        // Check 4: Duplicate meter numbers
        $this->info('4. Checking for duplicate meter numbers...');
        $duplicateNumbers = WaterMeter::select('meter_number')
            ->groupBy('meter_number')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicateNumbers->count() > 0) {
            $issues++;
            $this->warn("   ⚠️  Found {$duplicateNumbers->count()} duplicate meter numbers");
            
            foreach ($duplicateNumbers as $duplicate) {
                $meters = WaterMeter::where('meter_number', $duplicate->meter_number)->get();
                $this->line("   📊 Meter number '{$duplicate->meter_number}' has {$meters->count()} duplicates");
                
                if ($fix) {
                    // Keep the first meter, rename others
                    foreach ($meters->skip(1) as $index => $meter) {
                        $newNumber = $this->generateUniqueNumber();
                        $this->line("   🔄 Renaming meter ID {$meter->id} from '{$meter->meter_number}' to '{$newNumber}'");
                        $meter->meter_number = $newNumber;
                        $meter->save();
                    }
                }
            }
            
            if ($fix) {
                $this->info("   ✅ Fixed duplicate meter numbers");
            } else {
                $this->line("   💡 Run with --fix to automatically rename duplicate meter numbers");
            }
        } else {
            $this->info("   ✅ No duplicate meter numbers found");
        }

        // Summary
        $this->newLine();
        if ($issues === 0) {
            $this->info("🎉 Database integrity check passed! No issues found.");
        } else {
            if ($fix) {
                $this->info("🔧 Database integrity issues have been fixed!");
            } else {
                $this->warn("⚠️  Found {$issues} integrity issues. Run with --fix to resolve them.");
            }
        }

        // Statistics
        $this->newLine();
        $this->info('📊 Database Statistics:');
        $this->table(['Entity', 'Count'], [
            ['Customers', Customer::count()],
            ['Water Meters', WaterMeter::count()],
            ['Meter Readings', MeterReading::count()],
            ['Bills', Bill::count()],
        ]);

        return Command::SUCCESS;
    }

    /**
     * Generate a unique meter number
     */
    private function generateUniqueNumber(): string
    {
        $year = date('y');
        
        do {
            $randomNumber = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $meterNumber = "{$year}{$randomNumber}";
            $exists = WaterMeter::where('meter_number', $meterNumber)->exists();
        } while ($exists);
        
        return $meterNumber;
    }
}
