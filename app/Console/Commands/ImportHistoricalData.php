<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Customer;
use App\Models\WaterMeter;
use App\Models\MeterReading;
use App\Models\Bill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ImportHistoricalData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:historical-data 
                            {type : Type of data to import (readings|bills|both)}
                            {--file= : Path to CSV file}
                            {--readings-file= : Path to meter readings CSV file}
                            {--bills-file= : Path to bills/payments CSV file}
                            {--validate-only : Only validate data without importing}
                            {--batch-size=100 : Number of records to process in each batch}
                            {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import historical meter readings and payment records from CSV files';

    private $defaultReaderUser;
    private $importStats = [
        'readings' => ['total' => 0, 'success' => 0, 'errors' => 0],
        'bills' => ['total' => 0, 'success' => 0, 'errors' => 0]
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 AquaBill by olexto - Historical Data Import');
        $this->newLine();

        $type = $this->argument('type');
        $validateOnly = $this->option('validate-only');

        // Get default reader user for meter readings
        $this->defaultReaderUser = User::where('role', 'meter_reader')->first();
        if (!$this->defaultReaderUser) {
            $this->defaultReaderUser = User::where('role', 'admin')->first();
        }

        if (!$this->defaultReaderUser) {
            $this->error('❌ No suitable user found for meter readings. Please ensure you have at least one admin or meter reader user.');
            return Command::FAILURE;
        }

        // Show instructions
        $this->displayInstructions($type);

        if (!$this->option('force') && !$validateOnly) {
            if (!$this->confirm('Are you ready to proceed with the import?')) {
                $this->info('Import cancelled.');
                return Command::SUCCESS;
            }
        }

        try {
            switch ($type) {
                case 'readings':
                    $this->importMeterReadings($validateOnly);
                    break;
                case 'bills':
                    $this->importBills($validateOnly);
                    break;
                case 'both':
                    $this->importMeterReadings($validateOnly);
                    $this->importBills($validateOnly);
                    break;
                default:
                    $this->error('Invalid type. Use: readings, bills, or both');
                    return Command::FAILURE;
            }

            $this->displaySummary($validateOnly);
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Import failed: ' . $e->getMessage());
            Log::error('Historical data import failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return Command::FAILURE;
        }
    }

    /**
     * Display detailed instructions for CSV format
     */
    private function displayInstructions($type)
    {
        $this->info('📋 CSV File Format Instructions:');
        $this->newLine();

        if ($type === 'readings' || $type === 'both') {
            $this->info('📊 METER READINGS CSV FORMAT:');
            $this->line('Required columns (case-sensitive):');
            $this->line('  • customer_account_number - Customer account number (e.g., WB001)');
            $this->line('  • meter_number - Water meter number (e.g., MTR001)');
            $this->line('  • reading_date - Date in YYYY-MM-DD format');
            $this->line('  • current_reading - Numeric value (e.g., 1250.5)');
            $this->line('  • reading_type - actual, estimated, or customer_read');
            $this->line('  • reader_name - Name of person who took reading (optional)');
            $this->line('  • notes - Additional notes (optional)');
            $this->newLine();
            $this->info('📄 Example meter readings CSV:');
            $this->line('customer_account_number,meter_number,reading_date,current_reading,reading_type,reader_name,notes');
            $this->line('WB001,MTR001,2024-01-15,1250.5,actual,John Silva,Normal reading');
            $this->line('WB002,MTR002,2024-01-15,890.0,actual,Mary Fernando,');
            $this->newLine();
        }

        if ($type === 'bills' || $type === 'both') {
            $this->info('💰 BILLS/PAYMENTS CSV FORMAT:');
            $this->line('Required columns (case-sensitive):');
            $this->line('  • customer_account_number - Customer account number');
            $this->line('  • bill_date - Bill date in YYYY-MM-DD format');
            $this->line('  • due_date - Due date in YYYY-MM-DD format');
            $this->line('  • total_amount - Total bill amount');
            $this->line('  • paid_amount - Amount paid (0 if unpaid)');
            $this->line('  • payment_date - Payment date in YYYY-MM-DD format (optional)');
            $this->line('  • payment_method - cash, bank, online, cheque (optional)');
            $this->line('  • consumption - Water consumption in units');
            $this->line('  • notes - Additional notes (optional)');
            $this->newLine();
            $this->info('📄 Example bills CSV:');
            $this->line('customer_account_number,bill_date,due_date,total_amount,paid_amount,payment_date,payment_method,consumption,notes');
            $this->line('WB001,2024-01-01,2024-01-31,1500.00,1500.00,2024-01-25,cash,25,Paid on time');
            $this->line('WB002,2024-01-01,2024-01-31,2100.00,0.00,,,35,Unpaid bill');
            $this->newLine();
        }

        $this->info('💡 Important Notes:');
        $this->line('  • CSV files must have headers as the first row');
        $this->line('  • Dates must be in YYYY-MM-DD format');
        $this->line('  • Use decimal points for amounts (e.g., 1250.50)');
        $this->line('  • Customer account numbers and meter numbers must exist in the system');
        $this->line('  • Historical data will be imported with timestamps preserved');
        $this->newLine();
    }

    /**
     * Import meter readings from CSV
     */
    private function importMeterReadings($validateOnly = false)
    {
        $file = $this->option('readings-file') ?? $this->option('file');
        
        if (!$file) {
            $file = $this->ask('Enter the path to your meter readings CSV file:');
        }

        if (!file_exists($file)) {
            $this->error("❌ File not found: {$file}");
            return;
        }

        $this->info("📊 Processing meter readings from: {$file}");
        $this->newLine();

        $csvData = $this->readCsvFile($file);
        $this->importStats['readings']['total'] = count($csvData);

        if (empty($csvData)) {
            $this->warn('⚠️  No data found in CSV file');
            return;
        }

        // Validate required columns
        $requiredColumns = ['customer_account_number', 'meter_number', 'reading_date', 'current_reading', 'reading_type'];
        $headers = array_keys($csvData[0]);
        $missingColumns = array_diff($requiredColumns, $headers);

        if (!empty($missingColumns)) {
            $this->error('❌ Missing required columns: ' . implode(', ', $missingColumns));
            return;
        }

        $batchSize = $this->option('batch-size');
        $batches = array_chunk($csvData, $batchSize);
        $errors = [];

        foreach ($batches as $batchIndex => $batch) {
            $this->info("Processing batch " . ($batchIndex + 1) . "/" . count($batches) . " ({$batchSize} records)");
            
            if (!$validateOnly) {
                DB::beginTransaction();
            }

            try {
                foreach ($batch as $index => $row) {
                    $result = $this->processMeterReading($row, $validateOnly);
                    
                    if ($result['success']) {
                        $this->importStats['readings']['success']++;
                    } else {
                        $this->importStats['readings']['errors']++;
                        $errors[] = "Row " . ($index + 1) . ": " . $result['error'];
                    }
                }

                if (!$validateOnly) {
                    DB::commit();
                }

            } catch (\Exception $e) {
                if (!$validateOnly) {
                    DB::rollBack();
                }
                $errors[] = "Batch " . ($batchIndex + 1) . " failed: " . $e->getMessage();
            }
        }

        // Display errors
        if (!empty($errors)) {
            $this->newLine();
            $this->warn('⚠️  Import errors:');
            foreach (array_slice($errors, 0, 10) as $error) {
                $this->line("  • {$error}");
            }
            if (count($errors) > 10) {
                $this->line("  ... and " . (count($errors) - 10) . " more errors");
            }
        }
    }

    /**
     * Process a single meter reading row
     */
    private function processMeterReading($row, $validateOnly = false)
    {
        try {
            // Validate data
            $validator = Validator::make($row, [
                'customer_account_number' => 'required|string',
                'meter_number' => 'required|string',
                'reading_date' => 'required|date',
                'current_reading' => 'required|numeric|min:0',
                'reading_type' => 'required|in:actual,estimated,customer_read',
                'reader_name' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return ['success' => false, 'error' => implode(', ', $validator->errors()->all())];
            }

            // Find customer
            $customer = Customer::where('account_number', $row['customer_account_number'])->first();
            if (!$customer) {
                return ['success' => false, 'error' => "Customer not found: {$row['customer_account_number']}"];
            }

            // Find water meter
            $waterMeter = WaterMeter::where('meter_number', $row['meter_number'])
                ->where('customer_id', $customer->id)
                ->first();
            
            if (!$waterMeter) {
                return ['success' => false, 'error' => "Water meter not found: {$row['meter_number']} for customer {$row['customer_account_number']}"];
            }

            // Check for duplicate reading
            $existingReading = MeterReading::where('water_meter_id', $waterMeter->id)
                ->whereDate('reading_date', $row['reading_date'])
                ->first();

            if ($existingReading) {
                return ['success' => false, 'error' => "Reading already exists for {$row['meter_number']} on {$row['reading_date']}"];
            }

            if ($validateOnly) {
                return ['success' => true, 'error' => null];
            }

            // Get previous reading for consumption calculation
            $previousReading = MeterReading::where('water_meter_id', $waterMeter->id)
                ->where('reading_date', '<', $row['reading_date'])
                ->orderBy('reading_date', 'desc')
                ->first();

            $previousReadingValue = $previousReading ? $previousReading->current_reading : $waterMeter->initial_reading;
            $consumption = $row['current_reading'] - $previousReadingValue;

            // Validate reading is not decreasing
            if ($consumption < 0) {
                return ['success' => false, 'error' => "Current reading ({$row['current_reading']}) is less than previous reading ({$previousReadingValue})"];
            }

            // Create meter reading
            $meterReading = MeterReading::create([
                'water_meter_id' => $waterMeter->id,
                'reading_date' => $row['reading_date'],
                'current_reading' => $row['current_reading'],
                'previous_reading' => $previousReadingValue,
                'consumption' => $consumption,
                'reading_type' => $row['reading_type'],
                'reader_name' => $row['reader_name'] ?? $this->defaultReaderUser->name,
                'reader_id' => $this->defaultReaderUser->id,
                'notes' => $row['notes'] ?? null,
                'status' => 'verified',
                'created_at' => Carbon::parse($row['reading_date']),
                'updated_at' => Carbon::parse($row['reading_date'])
            ]);

            return ['success' => true, 'error' => null];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Import bills from CSV
     */
    private function importBills($validateOnly = false)
    {
        $file = $this->option('bills-file') ?? $this->option('file');
        
        if (!$file) {
            $file = $this->ask('Enter the path to your bills CSV file:');
        }

        if (!file_exists($file)) {
            $this->error("❌ File not found: {$file}");
            return;
        }

        $this->info("💰 Processing bills from: {$file}");
        $this->newLine();

        $csvData = $this->readCsvFile($file);
        $this->importStats['bills']['total'] = count($csvData);

        if (empty($csvData)) {
            $this->warn('⚠️  No data found in CSV file');
            return;
        }

        // Validate required columns
        $requiredColumns = ['customer_account_number', 'bill_date', 'due_date', 'total_amount', 'paid_amount', 'consumption'];
        $headers = array_keys($csvData[0]);
        $missingColumns = array_diff($requiredColumns, $headers);

        if (!empty($missingColumns)) {
            $this->error('❌ Missing required columns: ' . implode(', ', $missingColumns));
            return;
        }

        $batchSize = $this->option('batch-size');
        $batches = array_chunk($csvData, $batchSize);
        $errors = [];

        foreach ($batches as $batchIndex => $batch) {
            $this->info("Processing batch " . ($batchIndex + 1) . "/" . count($batches) . " ({$batchSize} records)");
            
            if (!$validateOnly) {
                DB::beginTransaction();
            }

            try {
                foreach ($batch as $index => $row) {
                    $result = $this->processBill($row, $validateOnly);
                    
                    if ($result['success']) {
                        $this->importStats['bills']['success']++;
                    } else {
                        $this->importStats['bills']['errors']++;
                        $errors[] = "Row " . ($index + 1) . ": " . $result['error'];
                    }
                }

                if (!$validateOnly) {
                    DB::commit();
                }

            } catch (\Exception $e) {
                if (!$validateOnly) {
                    DB::rollBack();
                }
                $errors[] = "Batch " . ($batchIndex + 1) . " failed: " . $e->getMessage();
            }
        }

        // Display errors
        if (!empty($errors)) {
            $this->newLine();
            $this->warn('⚠️  Import errors:');
            foreach (array_slice($errors, 0, 10) as $error) {
                $this->line("  • {$error}");
            }
            if (count($errors) > 10) {
                $this->line("  ... and " . (count($errors) - 10) . " more errors");
            }
        }
    }

    /**
     * Process a single bill row
     */
    private function processBill($row, $validateOnly = false)
    {
        try {
            // Validate data
            $validator = Validator::make($row, [
                'customer_account_number' => 'required|string',
                'bill_date' => 'required|date',
                'due_date' => 'required|date',
                'total_amount' => 'required|numeric|min:0',
                'paid_amount' => 'required|numeric|min:0',
                'payment_date' => 'nullable|date',
                'payment_method' => 'nullable|in:cash,bank,online,cheque',
                'consumption' => 'required|numeric|min:0',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return ['success' => false, 'error' => implode(', ', $validator->errors()->all())];
            }

            // Find customer
            $customer = Customer::where('account_number', $row['customer_account_number'])->first();
            if (!$customer) {
                return ['success' => false, 'error' => "Customer not found: {$row['customer_account_number']}"];
            }

            // Check for duplicate bill
            $existingBill = Bill::where('customer_id', $customer->id)
                ->whereDate('bill_date', $row['bill_date'])
                ->first();

            if ($existingBill) {
                return ['success' => false, 'error' => "Bill already exists for {$row['customer_account_number']} on {$row['bill_date']}"];
            }

            if ($validateOnly) {
                return ['success' => true, 'error' => null];
            }

            // Generate bill number
            $billNumber = 'BILL-' . Carbon::parse($row['bill_date'])->format('Ym') . '-' . str_pad($customer->id, 4, '0', STR_PAD_LEFT);

            // Determine status
            $paidAmount = floatval($row['paid_amount']);
            $totalAmount = floatval($row['total_amount']);
            
            if ($paidAmount >= $totalAmount) {
                $status = 'paid';
            } elseif ($paidAmount > 0) {
                $status = 'partial';
            } else {
                $status = 'unpaid';
            }

            // Create bill
            $bill = Bill::create([
                'customer_id' => $customer->id,
                'bill_number' => $billNumber,
                'bill_date' => $row['bill_date'],
                'due_date' => $row['due_date'],
                'consumption' => $row['consumption'],
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance' => $totalAmount - $paidAmount,
                'status' => $status,
                'payment_date' => $row['payment_date'] ?? null,
                'payment_method' => $row['payment_method'] ?? null,
                'notes' => $row['notes'] ?? null,
                'created_at' => Carbon::parse($row['bill_date']),
                'updated_at' => Carbon::parse($row['bill_date'])
            ]);

            return ['success' => true, 'error' => null];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Read CSV file and return array of data
     */
    private function readCsvFile($file)
    {
        $data = [];
        
        if (($handle = fopen($file, 'r')) !== FALSE) {
            $headers = fgetcsv($handle);
            
            while (($row = fgetcsv($handle)) !== FALSE) {
                if (count($row) === count($headers)) {
                    $data[] = array_combine($headers, $row);
                }
            }
            
            fclose($handle);
        }

        return $data;
    }

    /**
     * Display import summary
     */
    private function displaySummary($validateOnly = false)
    {
        $this->newLine();
        $this->info($validateOnly ? '✅ Validation Summary:' : '✅ Import Summary:');
        $this->newLine();

        if ($this->importStats['readings']['total'] > 0) {
            $this->info('📊 METER READINGS:');
            $this->line("  Total processed: {$this->importStats['readings']['total']}");
            $this->line("  Successful: {$this->importStats['readings']['success']}");
            $this->line("  Errors: {$this->importStats['readings']['errors']}");
            $this->newLine();
        }

        if ($this->importStats['bills']['total'] > 0) {
            $this->info('💰 BILLS:');
            $this->line("  Total processed: {$this->importStats['bills']['total']}");
            $this->line("  Successful: {$this->importStats['bills']['success']}");
            $this->line("  Errors: {$this->importStats['bills']['errors']}");
            $this->newLine();
        }

        if (!$validateOnly) {
            $this->info('🎯 Next Steps:');
            $this->line('  1. Verify imported data in the web interface');
            $this->line('  2. Run database integrity check: php artisan db:check-integrity');
            $this->line('  3. Generate missing bills if needed');
            $this->line('  4. Review and verify payment records');
            $this->newLine();
            $this->info('🌐 Access your system: http://127.0.0.1:8000');
        }
    }
} 