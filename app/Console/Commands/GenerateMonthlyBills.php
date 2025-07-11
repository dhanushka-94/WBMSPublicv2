<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\WaterMeter;
use App\Models\MeterReading;
use App\Models\Rate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyBills extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:generate-monthly 
                            {--force : Force generation even if not the 20th}
                            {--date= : Specific date to generate bills for (Y-m-d format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly water bills for all customers (runs on 20th of each month)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting monthly bill generation...');

        // Check if it's the 20th of the month or if force flag is used
        $today = Carbon::now();
        $force = $this->option('force');
        $specificDate = $this->option('date');

        if ($specificDate) {
            $billDate = Carbon::createFromFormat('Y-m-d', $specificDate)->day(20);
        } else {
            $billDate = $today->copy()->day(20);
        }

        if (!$force && !$specificDate && $today->day !== 20) {
            $this->error('Bills are generated on the 20th of each month. Use --force to override.');
            return 1;
        }

        $this->info("Generating bills for: {$billDate->format('Y-m-d')}");

        try {
            DB::beginTransaction();

            $dueDate = $billDate->copy()->addDays(30);
            $billingPeriodFrom = $billDate->copy()->startOfMonth();
            $billingPeriodTo = $billDate->copy()->endOfMonth();

            $generatedCount = 0;
            $errors = [];
            $skippedCount = 0;

            // Get all active customers with their active water meters
            $customers = Customer::active()
                ->with(['waterMeters' => function ($query) {
                    $query->where('status', 'active');
                }])
                ->get();

            $this->info("Found {$customers->count()} active customers");

            $progressBar = $this->output->createProgressBar($customers->count());
            $progressBar->start();

            foreach ($customers as $customer) {
                foreach ($customer->waterMeters as $meter) {
                    try {
                        // Check if bill already exists for this period
                        $existingBill = Bill::where('customer_id', $customer->id)
                            ->where('water_meter_id', $meter->id)
                            ->whereMonth('bill_date', $billDate->month)
                            ->whereYear('bill_date', $billDate->year)
                            ->first();

                        if ($existingBill) {
                            $skippedCount++;
                            continue; // Skip if bill already exists
                        }

                        // Get latest meter reading
                        $latestReading = $meter->meterReadings()
                            ->where('reading_date', '<=', $billDate)
                            ->latest('reading_date')
                            ->first();

                        if (!$latestReading) {
                            $errors[] = "No meter reading found for customer {$customer->full_name} (Meter: {$meter->meter_number})";
                            continue;
                        }

                        // Get previous reading
                        $previousReading = $meter->meterReadings()
                            ->where('reading_date', '<', $latestReading->reading_date)
                            ->latest('reading_date')
                            ->first();

                        $prevReading = $previousReading ? $previousReading->current_reading : $meter->initial_reading;
                        $consumption = max(0, $latestReading->current_reading - $prevReading);

                        // Calculate charges
                        $customerType = $customer->customerType->type ?? 'residential';
                        $charges = Rate::calculateCharges($customerType, $consumption, $billDate);

                        // Create bill
                        $bill = Bill::create([
                            'customer_id' => $customer->id,
                            'water_meter_id' => $meter->id,
                            'meter_reading_id' => $latestReading->id,
                            'bill_date' => $billDate,
                            'due_date' => $dueDate,
                            'billing_period_from' => $billingPeriodFrom,
                            'billing_period_to' => $billingPeriodTo,
                            'previous_reading' => $prevReading,
                            'current_reading' => $latestReading->current_reading,
                            'consumption' => $consumption,
                            'water_charges' => $charges['water_charges'],
                            'fixed_charges' => $charges['fixed_charges'],
                            'rate_breakdown' => $charges['breakdown'],
                            'status' => 'generated'
                        ]);

                        // Mark reading as billed
                        $latestReading->markAsBilled();
                        $generatedCount++;

                        // Log successful bill generation
                        Log::info("Bill generated: {$bill->bill_number} for customer {$customer->full_name}");

                    } catch (\Exception $e) {
                        $errors[] = "Error generating bill for customer {$customer->full_name} (Meter: {$meter->meter_number}): " . $e->getMessage();
                        Log::error("Error generating bill for customer {$customer->id}: " . $e->getMessage());
                    }
                }
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();

            DB::commit();

            // Display results
            $this->info("Bill generation completed!");
            $this->info("Generated: {$generatedCount} bills");
            $this->info("Skipped: {$skippedCount} bills (already exist)");

            if (!empty($errors)) {
                $this->error("Errors encountered:");
                foreach ($errors as $error) {
                    $this->error("- {$error}");
                }
            }

            // Log summary
            Log::info("Monthly bill generation completed", [
                'generated_count' => $generatedCount,
                'skipped_count' => $skippedCount,
                'error_count' => count($errors),
                'bill_date' => $billDate->format('Y-m-d'),
                'due_date' => $dueDate->format('Y-m-d')
            ]);

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to generate bills: " . $e->getMessage());
            Log::error("Monthly bill generation failed: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Get bill generation statistics
     */
    private function getBillStatistics($billDate)
    {
        $stats = [
            'total_customers' => Customer::active()->count(),
            'total_meters' => WaterMeter::active()->count(),
            'existing_bills' => Bill::whereMonth('bill_date', $billDate->month)
                ->whereYear('bill_date', $billDate->year)
                ->count(),
            'pending_readings' => MeterReading::pending()->count(),
        ];

        return $stats;
    }

    /**
     * Validate system readiness for bill generation
     */
    private function validateSystemReadiness()
    {
        $issues = [];

        // Check if there are active rates
        $activeRates = Rate::active()->count();
        if ($activeRates === 0) {
            $issues[] = "No active billing rates found";
        }

        // Check if there are active customers
        $activeCustomers = Customer::active()->count();
        if ($activeCustomers === 0) {
            $issues[] = "No active customers found";
        }

        // Check if there are active meters
        $activeMeters = WaterMeter::active()->count();
        if ($activeMeters === 0) {
            $issues[] = "No active water meters found";
        }

        return $issues;
    }

    /**
     * Send notifications after bill generation
     */
    private function sendNotifications($generatedCount, $errors)
    {
        // Here you could implement email notifications to administrators
        // about the bill generation results
        
        if ($generatedCount > 0) {
            Log::info("Bill generation notification: {$generatedCount} bills generated successfully");
        }

        if (!empty($errors)) {
            Log::warning("Bill generation errors", ['errors' => $errors]);
        }
    }
}
