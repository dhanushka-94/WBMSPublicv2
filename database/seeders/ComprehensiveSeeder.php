<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Customer;
use App\Models\Division;
use App\Models\CustomerType;
use App\Models\Guarantor;
use App\Models\Rate;
use App\Models\WaterMeter;
use App\Models\MeterReading;
use App\Models\Bill;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class ComprehensiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🚀 Starting comprehensive data generation...\n";
        
        // Create Users (100+)
        $this->createUsers();
        
        // Create Divisions (100+)
        $this->createDivisions();
        
        // Create Customer Types (10+)
        $this->createCustomerTypes();
        
        // Create Guarantors (100+)
        $this->createGuarantors();
        
        // Create Rates (100+)
        $this->createRates();
        
        // Create Customers (100+)
        $this->createCustomers();
        
        // Create Water Meters (100+)
        $this->createWaterMeters();
        
        // Create Meter Readings (500+)
        $this->createMeterReadings();
        
        // Create Bills (200+)
        $this->createBills();
        
        echo "\n🎉 Comprehensive data generation completed!\n";
        echo "📊 Generated data summary:\n";
        echo "   ✅ Users: " . User::count() . "\n";
        echo "   ✅ Divisions: " . Division::count() . "\n";
        echo "   ✅ Customer Types: " . CustomerType::count() . "\n";
        echo "   ✅ Guarantors: " . Guarantor::count() . "\n";
        echo "   ✅ Rates: " . Rate::count() . "\n";
        echo "   ✅ Customers: " . Customer::count() . "\n";
        echo "   ✅ Water Meters: " . WaterMeter::count() . "\n";
        echo "   ✅ Meter Readings: " . MeterReading::count() . "\n";
        echo "   ✅ Bills: " . Bill::count() . "\n";
        echo "\n";
    }
    
    private function createUsers(): void
    {
        echo "👥 Creating 100+ users...\n";
        
        // Create admin user
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@wassip.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        
        // Create meter readers
        for ($i = 1; $i <= 50; $i++) {
            User::create([
                'name' => "Meter Reader $i",
                'email' => "reader$i@wassip.com",
                'password' => Hash::make('password'),
                'role' => 'meter_reader',
                'email_verified_at' => now(),
            ]);
        }
        
        // Create staff
        for ($i = 1; $i <= 30; $i++) {
            User::create([
                'name' => "Staff Member $i",
                'email' => "staff$i@wassip.com",
                'password' => Hash::make('password'),
                'role' => 'staff',
                'email_verified_at' => now(),
            ]);
        }
        
        // Create managers
        for ($i = 1; $i <= 20; $i++) {
            User::create([
                'name' => "Manager $i",
                'email' => "manager$i@wassip.com",
                'password' => Hash::make('password'),
                'role' => 'manager',
                'email_verified_at' => now(),
            ]);
        }
        
        echo "   ✅ Created " . User::count() . " users\n";
    }
    
    private function createDivisions(): void
    {
        echo "🏢 Creating 100+ divisions...\n";
        
        $baseNames = [
            'MAHATMA GANDHIPURAM', 'GANDHIPURAM', 'MIDDLE DIVISION', 'FACTORY DIVISION',
            'LOWER DIVISION', 'UPPER', 'UPPER HOUSING', 'UPPER PUNDULOYA SHEEN', 'NORTH',
            'SOUTH', 'EAST', 'WEST', 'CENTRAL', 'DOWNTOWN', 'SUBURBAN', 'INDUSTRIAL',
            'RESIDENTIAL', 'COMMERCIAL', 'AGRICULTURAL', 'HILLSIDE'
        ];
        
        $areas = [
            'SECTOR A', 'SECTOR B', 'SECTOR C', 'SECTOR D', 'ZONE 1', 'ZONE 2', 'ZONE 3',
            'DISTRICT', 'BLOCK', 'AREA', 'REGION', 'WARD', 'COLONY', 'ESTATE', 'GARDENS'
        ];
        
        for ($i = 1; $i <= 120; $i++) {
            $baseName = $baseNames[array_rand($baseNames)];
            $area = $areas[array_rand($areas)];
            
            Division::create([
                'name' => "$baseName $area $i",
                'custom_id' => 'D' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'description' => "Division $i in $baseName $area",
                'is_active' => rand(0, 10) > 1, // 90% active
            ]);
        }
        
        echo "   ✅ Created " . Division::count() . " divisions\n";
    }
    
    private function createCustomerTypes(): void
    {
        echo "📋 Creating customer types...\n";
        
        $types = [
            ['name' => 'Residential', 'custom_id' => 'RES', 'description' => 'Residential customers'],
            ['name' => 'Commercial', 'custom_id' => 'COM', 'description' => 'Commercial businesses'],
            ['name' => 'Industrial', 'custom_id' => 'IND', 'description' => 'Industrial facilities'],
            ['name' => 'Agricultural', 'custom_id' => 'AGR', 'description' => 'Agricultural users'],
            ['name' => 'Government', 'custom_id' => 'GOV', 'description' => 'Government institutions'],
            ['name' => 'Educational', 'custom_id' => 'EDU', 'description' => 'Schools and universities'],
            ['name' => 'Healthcare', 'custom_id' => 'HEA', 'description' => 'Hospitals and clinics'],
            ['name' => 'Religious', 'custom_id' => 'REL', 'description' => 'Religious institutions'],
            ['name' => 'Non-Profit', 'custom_id' => 'NPO', 'description' => 'Non-profit organizations'],
            ['name' => 'Hospitality', 'custom_id' => 'HOS', 'description' => 'Hotels and restaurants'],
            ['name' => 'Retail', 'custom_id' => 'RET', 'description' => 'Retail establishments'],
            ['name' => 'Construction', 'custom_id' => 'CON', 'description' => 'Construction sites'],
            ['name' => 'Transportation', 'custom_id' => 'TRA', 'description' => 'Transportation facilities'],
            ['name' => 'Entertainment', 'custom_id' => 'ENT', 'description' => 'Entertainment venues'],
            ['name' => 'Sports', 'custom_id' => 'SPO', 'description' => 'Sports facilities'],
        ];
        
        foreach ($types as $type) {
            CustomerType::create($type);
        }
        
        echo "   ✅ Created " . CustomerType::count() . " customer types\n";
    }
    
    private function createGuarantors(): void
    {
        echo "🛡️ Creating 100+ guarantors...\n";
        
        $firstNames = [
            'John', 'Jane', 'Michael', 'Sarah', 'David', 'Lisa', 'Robert', 'Emily',
            'James', 'Jennifer', 'William', 'Amanda', 'Christopher', 'Ashley', 'Daniel',
            'Jessica', 'Matthew', 'Samantha', 'Anthony', 'Nicole', 'Mark', 'Elizabeth',
            'Donald', 'Megan', 'Steven', 'Stephanie', 'Paul', 'Michelle', 'Andrew', 'Rachel'
        ];
        
        $lastNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
            'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson',
            'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson',
            'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson'
        ];
        
        $relationships = ['Father', 'Mother', 'Spouse', 'Brother', 'Sister', 'Son', 'Daughter', 'Friend', 'Colleague', 'Neighbor'];
        
        for ($i = 1; $i <= 150; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            
            Guarantor::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'nic' => $this->generateNIC(),
                'phone' => $this->generatePhone(),
                'email' => strtolower($firstName . '.' . $lastName . rand(1, 999) . '@example.com'),
                'address' => $this->generateAddress(),
                'relationship' => $relationships[array_rand($relationships)],
                'is_active' => rand(0, 10) > 1, // 90% active
            ]);
        }
        
        echo "   ✅ Created " . Guarantor::count() . " guarantors\n";
    }
    
    private function createRates(): void
    {
        echo "💰 Creating 100+ rates...\n";
        
        $customerTypes = ['residential', 'commercial', 'industrial'];
        
        foreach ($customerTypes as $customerType) {
            // Create different rate variations for each customer type
            for ($variation = 1; $variation <= 5; $variation++) {
                $baseRate = rand(10, 20);
                $fixedCharge = rand(100, 300);
                $effectiveFrom = Carbon::now()->subMonths(rand(1, 24));
                $effectiveTo = rand(0, 1) ? $effectiveFrom->copy()->addMonths(rand(6, 12)) : null;
                
                $rateStructure = [
                    [
                        'name' => "Fixed Charge (0 units) - Variation $variation",
                        'customer_type' => $customerType,
                        'tier_from' => 0,
                        'tier_to' => 0,
                        'rate_per_unit' => 0,
                        'fixed_charge' => $fixedCharge,
                        'is_active' => rand(0, 3) > 0, // 75% active
                        'effective_from' => $effectiveFrom,
                        'effective_to' => $effectiveTo,
                        'description' => "Fixed monthly charge for 0 units consumption - $customerType Variation $variation"
                    ],
                    [
                        'name' => "Free Allowance (0-5 units) - Variation $variation",
                        'customer_type' => $customerType,
                        'tier_from' => 0,
                        'tier_to' => 5,
                        'rate_per_unit' => 0,
                        'fixed_charge' => 0,
                        'is_active' => rand(0, 3) > 0,
                        'effective_from' => $effectiveFrom,
                        'effective_to' => $effectiveTo,
                        'description' => "Free allowance for first 5 units - $customerType Variation $variation"
                    ],
                    [
                        'name' => "Tier 1 (6-10 units) - Variation $variation",
                        'customer_type' => $customerType,
                        'tier_from' => 6,
                        'tier_to' => 10,
                        'rate_per_unit' => $baseRate,
                        'fixed_charge' => 0,
                        'is_active' => rand(0, 3) > 0,
                        'effective_from' => $effectiveFrom,
                        'effective_to' => $effectiveTo,
                        'description' => "Rs. $baseRate per unit for 6-10 units - $customerType Variation $variation"
                    ],
                    [
                        'name' => "Tier 2 (11-15 units) - Variation $variation",
                        'customer_type' => $customerType,
                        'tier_from' => 11,
                        'tier_to' => 15,
                        'rate_per_unit' => $baseRate + rand(5, 10),
                        'fixed_charge' => 0,
                        'is_active' => rand(0, 3) > 0,
                        'effective_from' => $effectiveFrom,
                        'effective_to' => $effectiveTo,
                        'description' => "Tier 2 rate for 11-15 units - $customerType Variation $variation"
                    ],
                    [
                        'name' => "Tier 3 (16-20 units) - Variation $variation",
                        'customer_type' => $customerType,
                        'tier_from' => 16,
                        'tier_to' => 20,
                        'rate_per_unit' => $baseRate + rand(10, 20),
                        'fixed_charge' => 0,
                        'is_active' => rand(0, 3) > 0,
                        'effective_from' => $effectiveFrom,
                        'effective_to' => $effectiveTo,
                        'description' => "Tier 3 rate for 16-20 units - $customerType Variation $variation"
                    ],
                    [
                        'name' => "Tier 4 (21-25 units) - Variation $variation",
                        'customer_type' => $customerType,
                        'tier_from' => 21,
                        'tier_to' => 25,
                        'rate_per_unit' => $baseRate + rand(20, 30),
                        'fixed_charge' => 0,
                        'is_active' => rand(0, 3) > 0,
                        'effective_from' => $effectiveFrom,
                        'effective_to' => $effectiveTo,
                        'description' => "Tier 4 rate for 21-25 units - $customerType Variation $variation"
                    ],
                    [
                        'name' => "Tier 5 (26+ units) - Variation $variation",
                        'customer_type' => $customerType,
                        'tier_from' => 26,
                        'tier_to' => null,
                        'rate_per_unit' => $baseRate + rand(25, 40),
                        'fixed_charge' => 0,
                        'is_active' => rand(0, 3) > 0,
                        'effective_from' => $effectiveFrom,
                        'effective_to' => $effectiveTo,
                        'description' => "Tier 5 rate for usage above 25 units - $customerType Variation $variation"
                    ]
                ];
                
                foreach ($rateStructure as $rate) {
                    Rate::create($rate);
                }
            }
        }
        
        echo "   ✅ Created " . Rate::count() . " rates\n";
    }
    
    private function createCustomers(): void
    {
        echo "👤 Creating 100+ customers...\n";
        
        $divisions = Division::all();
        $customerTypes = CustomerType::all();
        $guarantors = Guarantor::all();
        
        $firstNames = [
            'John', 'Jane', 'Michael', 'Sarah', 'David', 'Lisa', 'Robert', 'Emily',
            'James', 'Jennifer', 'William', 'Amanda', 'Christopher', 'Ashley', 'Daniel',
            'Jessica', 'Matthew', 'Samantha', 'Anthony', 'Nicole', 'Mark', 'Elizabeth',
            'Donald', 'Megan', 'Steven', 'Stephanie', 'Paul', 'Michelle', 'Andrew', 'Rachel'
        ];
        
        $lastNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
            'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson',
            'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson',
            'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson'
        ];
        
        for ($i = 1; $i <= 150; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $division = $divisions->random();
            $customerType = $customerTypes->random();
            $guarantor = $guarantors->random();
            
            Customer::create([
                'account_number' => 'WAT' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'title' => rand(0, 1) ? 'Mr.' : 'Ms.',
                'first_name' => $firstName,
                'last_name' => $lastName,
                'nic' => $this->generateNIC(),
                'phone' => $this->generatePhone(),
                'email' => strtolower($firstName . '.' . $lastName . rand(1, 999) . '@example.com'),
                'address' => $this->generateAddress(),
                'city' => $this->generateCity(),
                'postal_code' => str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                'division_id' => $division->id,
                'customer_type_id' => $customerType->id,
                'guarantor_id' => rand(0, 3) == 0 ? $guarantor->id : null, // 25% have guarantors
                'connection_date' => Carbon::now()->subDays(rand(30, 1825)), // 1 month to 5 years ago
                'billing_day' => rand(1, 28),
                'status' => rand(0, 10) > 1 ? 'active' : 'inactive', // 90% active
                'notes' => rand(0, 5) == 0 ? 'Good payment history' : null,
                'auto_billing_enabled' => rand(0, 1),
                'next_billing_date' => Carbon::now()->addDays(rand(1, 30)),
            ]);
        }
        
        echo "   ✅ Created " . Customer::count() . " customers\n";
    }
    
    private function createWaterMeters(): void
    {
        echo "🌊 Creating 100+ water meters...\n";
        
        $customers = Customer::all();
        $brands = ['Sensus', 'Itron', 'Kamstrup', 'Elster', 'Neptune', 'Badger', 'Master Meter', 'Diehl', 'Takahata', 'Zenner'];
        $types = ['mechanical', 'digital', 'smart'];
        $statuses = ['active', 'inactive', 'faulty', 'replaced'];
        
        for ($i = 1; $i <= 150; $i++) {
            $customer = $customers->random();
            $brand = $brands[array_rand($brands)];
            $type = $types[array_rand($types)];
            
            WaterMeter::create([
                'meter_number' => 'WM' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'customer_id' => rand(0, 4) == 0 ? null : $customer->id, // 20% unassigned
                'meter_type' => $type,
                'meter_brand' => $brand,
                'meter_model' => $brand . ' ' . rand(100, 999),
                'initial_reading' => rand(0, 1000),
                'current_reading' => rand(1000, 10000),
                'installation_date' => Carbon::now()->subDays(rand(30, 1825)),
                'last_maintenance_date' => Carbon::now()->subDays(rand(1, 365)),
                'next_maintenance_date' => Carbon::now()->addDays(rand(30, 180)),
                'status' => $statuses[array_rand($statuses)],
                'location_notes' => $this->generateLocationNotes(),
                'latitude' => rand(0, 1) ? (6.9 + (rand(0, 1000) / 10000)) : null,
                'longitude' => rand(0, 1) ? (79.8 + (rand(0, 1000) / 10000)) : null,
                'notes' => rand(0, 5) == 0 ? 'Regular maintenance schedule' : null,
            ]);
        }
        
        echo "   ✅ Created " . WaterMeter::count() . " water meters\n";
    }
    
    private function createMeterReadings(): void
    {
        echo "📊 Creating 500+ meter readings...\n";
        
        $waterMeters = WaterMeter::all();
        $users = User::where('role', 'meter_reader')->get();
        
        foreach ($waterMeters as $meter) {
            $numReadings = rand(3, 8); // 3-8 readings per meter
            $lastReading = $meter->initial_reading;
            
            // Create unique dates for this meter
            $dates = [];
            for ($i = 0; $i < $numReadings; $i++) {
                do {
                    $date = Carbon::now()->subDays(rand(1, 365));
                    $dateKey = $date->format('Y-m-d');
                } while (in_array($dateKey, $dates));
                
                $dates[] = $dateKey;
                $currentReading = $lastReading + rand(10, 500);
                $user = $users->random();
                
                MeterReading::create([
                    'water_meter_id' => $meter->id,
                    'previous_reading' => $lastReading,
                    'current_reading' => $currentReading,
                    'consumption' => $currentReading - $lastReading,
                    'reading_date' => $date,
                    'reader_name' => $user->name,
                    'reader_id' => $user->id,
                    'reading_type' => rand(0, 3) == 0 ? 'estimated' : 'actual',
                    'status' => ['pending', 'verified', 'billed'][rand(0, 2)],
                    'notes' => rand(0, 5) == 0 ? 'Normal reading' : null,
                ]);
                
                $lastReading = $currentReading;
            }
        }
        
        echo "   ✅ Created " . MeterReading::count() . " meter readings\n";
    }
    
    private function createBills(): void
    {
        echo "💳 Creating 200+ bills...\n";
        
        $customers = Customer::whereHas('waterMeters')->get();
        
        foreach ($customers as $customer) {
            $numBills = rand(1, 4); // 1-4 bills per customer
            
            for ($i = 0; $i < $numBills; $i++) {
                $waterMeter = $customer->waterMeters->first(); // Get first water meter for this customer
                if (!$waterMeter) continue; // Skip if customer has no water meters
                
                $meterReading = $waterMeter->meterReadings->random(); // Get random reading for this meter
                if (!$meterReading) continue; // Skip if meter has no readings
                
                $billDate = Carbon::now()->subDays(rand(1, 365));
                $dueDate = $billDate->copy()->addDays(30);
                $billingPeriodFrom = $billDate->copy()->subDays(30);
                $billingPeriodTo = $billDate->copy();
                
                $consumption = rand(50, 500);
                $waterCharges = rand(800, 4000);
                $fixedCharges = rand(100, 500);
                $serviceCharges = rand(50, 200);
                $totalAmount = $waterCharges + $fixedCharges + $serviceCharges;
                $paidAmount = rand(0, 2) == 0 ? $totalAmount : rand(0, $totalAmount);
                $balanceAmount = $totalAmount - $paidAmount;
                
                Bill::create([
                    'bill_number' => 'BILL' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
                    'customer_id' => $customer->id,
                    'water_meter_id' => $waterMeter->id,
                    'meter_reading_id' => $meterReading->id,
                    'bill_date' => $billDate,
                    'due_date' => $dueDate,
                    'billing_period_from' => $billingPeriodFrom,
                    'billing_period_to' => $billingPeriodTo,
                    'previous_reading' => $meterReading->previous_reading,
                    'current_reading' => $meterReading->current_reading,
                    'consumption' => $meterReading->consumption,
                    'water_charges' => $waterCharges,
                    'fixed_charges' => $fixedCharges,
                    'service_charges' => $serviceCharges,
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'balance_amount' => $balanceAmount,
                    'status' => $balanceAmount > 0 ? (['generated', 'sent', 'overdue'][rand(0, 2)]) : 'paid',
                    'paid_at' => $paidAmount > 0 ? $billDate->copy()->addDays(rand(1, 45)) : null,
                    'notes' => rand(0, 5) == 0 ? 'Regular billing cycle' : null,
                ]);
            }
        }
        
        echo "   ✅ Created " . Bill::count() . " bills\n";
    }
    
    private function generateNIC(): string
    {
        return rand(100000000, 999999999) . 'V';
    }
    
    private function generatePhone(): string
    {
        return '+94' . rand(70, 78) . rand(1000000, 9999999);
    }
    
    private function generateAddress(): string
    {
        $numbers = [rand(1, 999), rand(1, 99) . '/' . rand(1, 9)];
        $streets = ['Main Street', 'Church Lane', 'Park Avenue', 'Hill Road', 'Garden Path', 'Lake View', 'Forest Road', 'River Side'];
        $areas = ['Kandy', 'Colombo', 'Galle', 'Matara', 'Kurunegala', 'Anuradhapura', 'Ratnapura', 'Badulla'];
        
        return $numbers[array_rand($numbers)] . ', ' . $streets[array_rand($streets)] . ', ' . $areas[array_rand($areas)];
    }
    
    private function generateCity(): string
    {
        $cities = ['Kandy', 'Colombo', 'Galle', 'Matara', 'Kurunegala', 'Anuradhapura', 'Ratnapura', 'Badulla', 'Jaffna', 'Batticaloa'];
        return $cities[array_rand($cities)];
    }
    
    private function generateOccupation(): string
    {
        $occupations = ['Engineer', 'Doctor', 'Teacher', 'Lawyer', 'Accountant', 'Manager', 'Technician', 'Nurse', 'Farmer', 'Business Owner'];
        return $occupations[array_rand($occupations)];
    }
    
    private function generateLocationNotes(): string
    {
        $notes = [
            'Near main gate',
            'Behind the house',
            'Next to garage',
            'Front yard',
            'Side of building',
            'Near water tank',
            'Garden area',
            'Basement level',
            'Ground floor',
            'Accessible from road'
        ];
        return $notes[array_rand($notes)];
    }
} 