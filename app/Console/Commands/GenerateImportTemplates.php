<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\WaterMeter;
use Carbon\Carbon;

class GenerateImportTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:generate-templates 
                            {--output-dir=storage/app/import-templates : Directory to save templates}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CSV templates for historical data import';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $outputDir = $this->option('output-dir');
        
        // Create output directory if it doesn't exist
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $this->info('📋 Generating CSV import templates...');
        $this->newLine();

        // Generate meter readings template
        $this->generateMeterReadingsTemplate($outputDir);
        
        // Generate bills template
        $this->generateBillsTemplate($outputDir);
        
        // Generate sample data templates
        $this->generateSampleDataTemplates($outputDir);

        $this->newLine();
        $this->info('✅ Templates generated successfully!');
        $this->info("📁 Templates saved in: {$outputDir}");
        $this->newLine();
        
        $this->info('📄 Generated files:');
        $this->line('  • meter_readings_template.csv - Empty template for meter readings');
        $this->line('  • bills_template.csv - Empty template for bills');
        $this->line('  • meter_readings_sample.csv - Sample data for meter readings');
        $this->line('  • bills_sample.csv - Sample data for bills');
        $this->newLine();
        
        $this->info('💡 Usage:');
        $this->line('  1. Copy your data into the template files');
        $this->line('  2. Validate: php artisan import:historical-data readings --file=path/to/file.csv --validate-only');
        $this->line('  3. Import: php artisan import:historical-data readings --file=path/to/file.csv');
    }

    /**
     * Generate meter readings template
     */
    private function generateMeterReadingsTemplate($outputDir)
    {
        $templateFile = $outputDir . '/meter_readings_template.csv';
        
        $headers = [
            'customer_account_number',
            'meter_number',
            'reading_date',
            'current_reading',
            'reading_type',
            'reader_name',
            'notes'
        ];

        $file = fopen($templateFile, 'w');
        fputcsv($file, $headers);
        fclose($file);

        $this->line("✅ Created: meter_readings_template.csv");
    }

    /**
     * Generate bills template
     */
    private function generateBillsTemplate($outputDir)
    {
        $templateFile = $outputDir . '/bills_template.csv';
        
        $headers = [
            'customer_account_number',
            'bill_date',
            'due_date',
            'total_amount',
            'paid_amount',
            'payment_date',
            'payment_method',
            'consumption',
            'notes'
        ];

        $file = fopen($templateFile, 'w');
        fputcsv($file, $headers);
        fclose($file);

        $this->line("✅ Created: bills_template.csv");
    }

    /**
     * Generate sample data templates
     */
    private function generateSampleDataTemplates($outputDir)
    {
        // Get some existing customers and meters for sample data
        $customers = Customer::with('waterMeter')->limit(3)->get();
        
        if ($customers->isEmpty()) {
            $this->warn('⚠️  No customers found. Sample data templates will use placeholder data.');
            $this->generatePlaceholderSamples($outputDir);
            return;
        }

        // Generate sample meter readings
        $sampleReadingsFile = $outputDir . '/meter_readings_sample.csv';
        $file = fopen($sampleReadingsFile, 'w');
        
        $headers = [
            'customer_account_number',
            'meter_number',
            'reading_date',
            'current_reading',
            'reading_type',
            'reader_name',
            'notes'
        ];
        fputcsv($file, $headers);

        foreach ($customers as $customer) {
            if ($customer->waterMeter) {
                // Generate 3 months of sample readings
                for ($i = 3; $i >= 1; $i--) {
                    $date = Carbon::now()->subMonths($i)->format('Y-m-d');
                    $reading = 1000 + ($i * 25); // Sample progressive readings
                    
                    fputcsv($file, [
                        $customer->account_number,
                        $customer->waterMeter->meter_number,
                        $date,
                        $reading,
                        'actual',
                        'John Silva',
                        'Historical import'
                    ]);
                }
            }
        }
        fclose($file);

        // Generate sample bills
        $sampleBillsFile = $outputDir . '/bills_sample.csv';
        $file = fopen($sampleBillsFile, 'w');
        
        $headers = [
            'customer_account_number',
            'bill_date',
            'due_date',
            'total_amount',
            'paid_amount',
            'payment_date',
            'payment_method',
            'consumption',
            'notes'
        ];
        fputcsv($file, $headers);

        foreach ($customers as $customer) {
            // Generate 3 months of sample bills
            for ($i = 3; $i >= 1; $i--) {
                $billDate = Carbon::now()->subMonths($i)->startOfMonth()->format('Y-m-d');
                $dueDate = Carbon::now()->subMonths($i)->endOfMonth()->format('Y-m-d');
                $consumption = 25 + ($i * 5); // Sample consumption
                $amount = 150 + ($consumption * 12); // Sample calculation
                $paid = $i > 1 ? $amount : 0; // Latest bill unpaid
                $paymentDate = $i > 1 ? Carbon::parse($dueDate)->subDays(5)->format('Y-m-d') : '';
                
                fputcsv($file, [
                    $customer->account_number,
                    $billDate,
                    $dueDate,
                    $amount,
                    $paid,
                    $paymentDate,
                    $paid > 0 ? 'cash' : '',
                    $consumption,
                    'Historical import'
                ]);
            }
        }
        fclose($file);

        $this->line("✅ Created: meter_readings_sample.csv");
        $this->line("✅ Created: bills_sample.csv");
    }

    /**
     * Generate placeholder sample data when no customers exist
     */
    private function generatePlaceholderSamples($outputDir)
    {
        // Generate sample meter readings with placeholder data
        $sampleReadingsFile = $outputDir . '/meter_readings_sample.csv';
        $file = fopen($sampleReadingsFile, 'w');
        
        $headers = [
            'customer_account_number',
            'meter_number',
            'reading_date',
            'current_reading',
            'reading_type',
            'reader_name',
            'notes'
        ];
        fputcsv($file, $headers);

        $sampleData = [
            ['WB001', 'MTR001', '2024-01-15', '1250.5', 'actual', 'John Silva', 'Normal reading'],
            ['WB001', 'MTR001', '2024-02-15', '1275.0', 'actual', 'John Silva', 'Normal reading'],
            ['WB002', 'MTR002', '2024-01-15', '890.0', 'actual', 'Mary Fernando', 'Normal reading'],
            ['WB002', 'MTR002', '2024-02-15', '920.5', 'estimated', 'Mary Fernando', 'Customer not available'],
        ];

        foreach ($sampleData as $row) {
            fputcsv($file, $row);
        }
        fclose($file);

        // Generate sample bills with placeholder data
        $sampleBillsFile = $outputDir . '/bills_sample.csv';
        $file = fopen($sampleBillsFile, 'w');
        
        $headers = [
            'customer_account_number',
            'bill_date',
            'due_date',
            'total_amount',
            'paid_amount',
            'payment_date',
            'payment_method',
            'consumption',
            'notes'
        ];
        fputcsv($file, $headers);

        $sampleBillData = [
            ['WB001', '2024-01-01', '2024-01-31', '1500.00', '1500.00', '2024-01-25', 'cash', '25', 'Paid on time'],
            ['WB001', '2024-02-01', '2024-02-29', '1650.00', '0.00', '', '', '30', 'Unpaid bill'],
            ['WB002', '2024-01-01', '2024-01-31', '2100.00', '2100.00', '2024-01-28', 'bank', '35', 'Bank transfer'],
            ['WB002', '2024-02-01', '2024-02-29', '1800.00', '1000.00', '2024-02-20', 'cash', '28', 'Partial payment'],
        ];

        foreach ($sampleBillData as $row) {
            fputcsv($file, $row);
        }
        fclose($file);

        $this->line("✅ Created: meter_readings_sample.csv (with placeholder data)");
        $this->line("✅ Created: bills_sample.csv (with placeholder data)");
    }
} 