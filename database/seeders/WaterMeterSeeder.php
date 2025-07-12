<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\WaterMeter;
use App\Models\MeterReading;
use Carbon\Carbon;

class WaterMeterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "Creating water meters for customers...\n";

        $customers = Customer::all();
        
        if ($customers->isEmpty()) {
            echo "No customers found. Please run CustomerSeeder first.\n";
            return;
        }

        $meterTypes = ['mechanical', 'digital', 'smart'];
        $meterBrands = ['Sensus', 'Itron', 'Elster', 'Badger', 'Neptune', 'Kamstrup'];
        $meterModels = ['WP-Dynamic', 'Aquadis+', 'H4000', 'E-Series', 'T-10', 'MultiCal'];
        $meterSizes = [15, 20, 25, 32, 40, 50]; // in mm

        foreach ($customers as $index => $customer) {
            // Create water meter for each customer (numbers only format)
            $year = date('y'); // Last 2 digits of year
            $meterNumber = $year . str_pad($index + 1, 6, '0', STR_PAD_LEFT);
            $initialReading = rand(1000, 5000);
            
            $waterMeter = WaterMeter::create([
                'customer_id' => $customer->id,
                'meter_number' => $meterNumber,
                'meter_brand' => $meterBrands[array_rand($meterBrands)],
                'meter_model' => $meterModels[array_rand($meterModels)],
                'meter_size' => $meterSizes[array_rand($meterSizes)],
                'meter_type' => $meterTypes[array_rand($meterTypes)],
                'installation_date' => $customer->connection_date ?? Carbon::now()->subDays(rand(30, 365)),
                'initial_reading' => $initialReading,
                'current_reading' => $initialReading,
                'latitude' => 6.9271 + (rand(-1000, 1000) / 10000), // Around Sri Lanka
                'longitude' => 79.8612 + (rand(-1000, 1000) / 10000),
                'address' => $customer->address,
                'status' => $customer->status === 'active' ? 'active' : 'inactive',
                'last_maintenance_date' => Carbon::now()->subDays(rand(30, 180)),
                'next_maintenance_date' => Carbon::now()->addDays(rand(30, 90)),
                'location_notes' => 'Located in ' . ['front yard', 'side yard', 'back yard', 'near gate', 'by road', 'garden area'][array_rand(['front yard', 'side yard', 'back yard', 'near gate', 'by road', 'garden area'])],
                'notes' => rand(0, 1) ? 'Installed during initial connection' : null
            ]);

            // Create sample meter readings for the past 6 months
            $this->createSampleReadings($waterMeter, $initialReading);

            echo "Created meter: {$meterNumber} for customer {$customer->full_name}\n";
        }

        echo "\n✅ Successfully created " . $customers->count() . " water meters!\n";
        echo "Total water meters: " . WaterMeter::count() . "\n";
        echo "Total meter readings: " . MeterReading::count() . "\n";
    }

    /**
     * Create sample meter readings for a water meter
     */
    private function createSampleReadings(WaterMeter $waterMeter, int $initialReading): void
    {
        $currentReading = $initialReading;
        $readingTypes = ['actual', 'actual', 'actual', 'estimated', 'customer_read'];
        $readerNames = ['John Silva', 'Mary Fernando', 'David Perera', 'Sarah Wickrama', 'Agent Smith'];

        // Create readings for the past 6 months
        for ($i = 6; $i >= 1; $i--) {
            $readingDate = Carbon::now()->subMonths($i)->day(rand(15, 25));
            
            // Skip if reading date is in the future
            if ($readingDate->isFuture()) {
                continue;
            }

            // Generate realistic consumption (15-80 units per month)
            $consumption = rand(15, 80);
            $currentReading += $consumption;

            // Occasionally have zero consumption (customer away, etc.)
            if (rand(1, 10) === 1) {
                $consumption = 0;
            }

            $reading = MeterReading::create([
                'water_meter_id' => $waterMeter->id,
                'reading_date' => $readingDate,
                'current_reading' => $currentReading,
                'reading_type' => $readingTypes[array_rand($readingTypes)],
                'reader_name' => $readerNames[array_rand($readerNames)],
                'status' => $i === 1 ? 'pending' : 'verified', // Latest reading pending
                'notes' => rand(0, 1) ? 'Regular monthly reading' : null,
                'created_at' => $readingDate,
                'updated_at' => $readingDate
            ]);

            // Mark older readings as billed (except the latest 2)
            if ($i > 2) {
                $reading->update(['status' => 'billed']);
            }
        }

        // Update the water meter's current reading to the latest
        $waterMeter->update(['current_reading' => $currentReading]);
    }
}
