<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CalculateLateFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:calculate-late-fees 
                            {--force : Force calculation even if not the 1st}
                            {--date= : Specific date to calculate fees for (Y-m-d format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and apply late fees to overdue bills';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting late fee calculation...');

        // Check if it's the 1st of the month or if force flag is used
        $today = Carbon::now();
        $force = $this->option('force');
        $specificDate = $this->option('date');

        if ($specificDate) {
            $calculationDate = Carbon::createFromFormat('Y-m-d', $specificDate);
        } else {
            $calculationDate = $today->copy();
        }

        if (!$force && !$specificDate && $today->day !== 1) {
            $this->error('Late fees are calculated on the 1st of each month. Use --force to override.');
            return 1;
        }

        $this->info("Calculating late fees for: {$calculationDate->format('Y-m-d')}");

        try {
            DB::beginTransaction();

            $updatedCount = 0;
            $errors = [];

            // Get all overdue bills that haven't been paid
            $overdueBills = Bill::where('status', '!=', 'paid')
                ->where('due_date', '<', $calculationDate)
                ->with(['customer', 'waterMeter'])
                ->get();

            $this->info("Found {$overdueBills->count()} overdue bills");

            if ($overdueBills->isEmpty()) {
                $this->info('No overdue bills found.');
                return 0;
            }

            $progressBar = $this->output->createProgressBar($overdueBills->count());
            $progressBar->start();

            foreach ($overdueBills as $bill) {
                try {
                    // Calculate days overdue
                    $daysOverdue = $calculationDate->diffInDays($bill->due_date);
                    
                    // Calculate monthly late fee (2% per month or Rs. 50 minimum)
                    $monthsOverdue = ceil($daysOverdue / 30);
                    $totalAmount = $bill->total_amount;
                    
                    // 2% per month
                    $percentageFee = ($totalAmount * 0.02) * $monthsOverdue;
                    
                    // Minimum Rs. 50 per month
                    $minimumFee = 50 * $monthsOverdue;
                    
                    // Use whichever is higher
                    $lateFee = max($percentageFee, $minimumFee);
                    
                    // Round to nearest rupee
                    $lateFee = round($lateFee, 2);

                    // Only update if late fee has changed
                    if ($bill->late_fee != $lateFee) {
                        $bill->update([
                            'late_fee' => $lateFee,
                            'status' => 'overdue'
                        ]);

                        $updatedCount++;

                        // Log the update
                        Log::info("Late fee updated for bill {$bill->bill_number}: Rs. {$lateFee} ({$daysOverdue} days overdue)");
                    }

                    // Update status to overdue if not already
                    if ($bill->status !== 'overdue' && $bill->status !== 'paid') {
                        $bill->update(['status' => 'overdue']);
                    }

                } catch (\Exception $e) {
                    $errors[] = "Error calculating late fee for bill {$bill->bill_number}: " . $e->getMessage();
                    Log::error("Error calculating late fee for bill {$bill->id}: " . $e->getMessage());
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();

            DB::commit();

            // Display results
            $this->info("Late fee calculation completed!");
            $this->info("Updated: {$updatedCount} bills");
            $this->info("Total overdue bills: {$overdueBills->count()}");

            if (!empty($errors)) {
                $this->error("Errors encountered:");
                foreach ($errors as $error) {
                    $this->error("- {$error}");
                }
            }

            // Calculate summary statistics
            $totalLateFees = $overdueBills->sum('late_fee');
            $totalOverdueAmount = $overdueBills->sum('total_amount');

            $this->info("Summary:");
            $this->info("- Total late fees: Rs. " . number_format($totalLateFees, 2));
            $this->info("- Total overdue amount: Rs. " . number_format($totalOverdueAmount, 2));
            $this->info("- Average days overdue: " . round($overdueBills->avg(function ($bill) use ($calculationDate) {
                return $calculationDate->diffInDays($bill->due_date);
            }), 1));

            // Log summary
            Log::info("Late fee calculation completed", [
                'updated_count' => $updatedCount,
                'total_overdue_bills' => $overdueBills->count(),
                'total_late_fees' => $totalLateFees,
                'total_overdue_amount' => $totalOverdueAmount,
                'calculation_date' => $calculationDate->format('Y-m-d')
            ]);

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to calculate late fees: " . $e->getMessage());
            Log::error("Late fee calculation failed: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Get late fee statistics
     */
    private function getLateFeeStatistics($calculationDate)
    {
        $stats = [
            'total_overdue_bills' => Bill::where('status', 'overdue')->count(),
            'total_late_fees' => Bill::where('status', 'overdue')->sum('late_fee'),
            'average_days_overdue' => Bill::where('status', 'overdue')
                ->get()
                ->avg(function ($bill) use ($calculationDate) {
                    return $calculationDate->diffInDays($bill->due_date);
                }),
            'oldest_overdue_bill' => Bill::where('status', 'overdue')
                ->orderBy('due_date', 'asc')
                ->first()
        ];

        return $stats;
    }

    /**
     * Send notifications about late fees
     */
    private function sendNotifications($updatedCount, $totalLateFees)
    {
        // This could be extended to send email notifications
        // to administrators or customers about late fees
        
        if ($updatedCount > 0) {
            $this->info("Notification: {$updatedCount} bills had late fees updated totaling Rs. " . number_format($totalLateFees, 2));
        }
    }
}
