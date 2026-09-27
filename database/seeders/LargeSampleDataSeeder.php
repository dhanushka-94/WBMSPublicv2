<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Division;
use App\Models\Guarantor;
use App\Models\MeterReading;
use App\Models\Rate;
use App\Models\User;
use App\Models\WaterMeter;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class LargeSampleDataSeeder extends Seeder
{
    private int $customerCount = 500;
    private int $guarantorCount = 120;
    private int $monthsOfHistory = 12;

    /** @var array<string, int> */
    private array $billSequence = [];

    public function run(): void
    {
        Config::set('sms.bill_notifications.new_bill', false);
        Config::set('sms.bill_notifications.due_reminder', false);
        Config::set('sms.bill_notifications.overdue_alert', false);
        Config::set('sms.bill_notifications.payment_confirmation', false);
        Config::set('sms.bill_notifications.late_fee_notice', false);
        Config::set('sms.notifications.enabled', false);

        $this->command?->info('Seeding large sample dataset...');
        $this->command?->line("  Customers: {$this->customerCount}");
        $this->command?->line("  Guarantors: {$this->guarantorCount}");
        $this->command?->line("  History months: {$this->monthsOfHistory}");

        $this->call([
            CustomerTypeSeeder::class,
            DivisionSeeder::class,
            RateSeeder::class,
        ]);

        // Expand divisions for volume variety
        $extraDivisions = [
            ['name' => 'Middle Division', 'custom_id' => 'MD', 'description' => 'Middle estate housing'],
            ['name' => 'Office Compound', 'custom_id' => 'OC', 'description' => 'Estate office area'],
            ['name' => 'Hospital Line', 'custom_id' => 'HL', 'description' => 'Medical quarters'],
            ['name' => 'School Area', 'custom_id' => 'SA', 'description' => 'School staff housing'],
            ['name' => 'Store Complex', 'custom_id' => 'SC', 'description' => 'Warehouse and stores'],
            ['name' => 'Lower Factory', 'custom_id' => 'LF', 'description' => 'Factory housing line'],
            ['name' => 'Upper Factory', 'custom_id' => 'UF', 'description' => 'Upper factory quarters'],
            ['name' => 'River Side', 'custom_id' => 'RS', 'description' => 'Riverside labour lines'],
            ['name' => 'Temple Road', 'custom_id' => 'TR', 'description' => 'Temple road housing'],
            ['name' => 'New Colony', 'custom_id' => 'NC', 'description' => 'New colony extension'],
            ['name' => 'Old Bazaar', 'custom_id' => 'OB', 'description' => 'Old bazaar shops'],
            ['name' => 'Pump Station', 'custom_id' => 'PS', 'description' => 'Pump station staff'],
            ['name' => 'Guest House', 'custom_id' => 'GH', 'description' => 'Guest house and bungalows'],
            ['name' => 'Nursery Line', 'custom_id' => 'NL', 'description' => 'Nursery and garden staff'],
        ];
        foreach ($extraDivisions as $division) {
            Division::firstOrCreate(['custom_id' => $division['custom_id']], array_merge($division, ['is_active' => true]));
        }

        $divisions = Division::all()->values();
        $types = CustomerType::all()->values();
        $readers = User::where('role', 'meter_reader')->get();

        if ($readers->isEmpty()) {
            $this->command?->error('No meter readers found. Run SystemUsersSeeder first.');
            return;
        }

        if ($divisions->isEmpty() || $types->isEmpty()) {
            $this->command?->error('Divisions or customer types missing.');
            return;
        }

        $guarantors = $this->seedGuarantors();
        $this->command?->info('Guarantors: ' . count($guarantors));

        $customers = $this->seedCustomers($divisions, $types, $guarantors);
        $this->command?->info('Customers: ' . count($customers));

        $meters = $this->seedMeters($customers);
        $this->command?->info('Meters: ' . count($meters));

        $this->seedReadingsAndBills($meters, $readers);
        $this->seedSmsNotifications();
        $this->seedActivityLogs();

        $this->command?->info('Large sample data seeded successfully.');
        $this->command?->table(
            ['Table', 'Count'],
            [
                ['Users', User::count()],
                ['Divisions', Division::count()],
                ['Customer types', CustomerType::count()],
                ['Rates', Rate::count()],
                ['Guarantors', Guarantor::count()],
                ['Customers', Customer::count()],
                ['Water meters', WaterMeter::count()],
                ['Meter readings', MeterReading::count()],
                ['Bills', Bill::count()],
                ['SMS notifications', DB::table('sms_notifications')->count()],
                ['Activity logs', DB::table('activity_logs')->count()],
            ]
        );
    }

    private function seedGuarantors(): array
    {
        $firstNames = ['Sunil', 'Kamala', 'Nimal', 'Ruwan', 'Chamari', 'Ajith', 'Dilani', 'Pradeep', 'Saman', 'Nadeesha', 'Kasun', 'Ishara', 'Gayan', 'Malsha', 'Tharindu'];
        $lastNames = ['Perera', 'Fernando', 'Silva', 'Jayasinghe', 'Bandara', 'Herath', 'Gunasekara', 'Wickramasinghe', 'Rathnayake', 'Senanayake'];
        $relationships = ['Father', 'Mother', 'Brother', 'Sister', 'Uncle', 'Friend', 'Colleague', 'Neighbor'];

        $guarantors = [];
        for ($i = 1; $i <= $this->guarantorCount; $i++) {
            $first = $firstNames[($i - 1) % count($firstNames)];
            $last = $lastNames[($i - 1) % count($lastNames)];
            $guarantors[] = Guarantor::create([
                'guarantor_id' => 'G' . str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'first_name' => $first,
                'last_name' => $last . $i,
                'nic' => sprintf('%d%07dV', 70 + ($i % 25), 2000000 + $i),
                'phone' => '07' . str_pad((string) (71100000 + $i), 8, '0', STR_PAD_LEFT),
                'email' => strtolower($first) . $i . '.guarantor@example.com',
                'address' => "No. {$i}, Estate Road, Dunsinane, Nuwara Eliya",
                'relationship' => $relationships[($i - 1) % count($relationships)],
                'is_active' => true,
            ]);
        }

        return $guarantors;
    }

    private function seedCustomers($divisions, $types, array $guarantors): array
    {
        $titles = ['Mr', 'Mrs', 'Ms', 'Miss', 'M/s'];
        $firstNames = ['Anura', 'Seetha', 'Mahinda', 'Nadeeka', 'Kasun', 'Thilini', 'Samantha', 'Priyanka', 'Roshan', 'Malithi', 'Gayan', 'Ishara', 'Nuwan', 'Sanduni', 'Chathura', 'Lanka', 'Pathum', 'Dinuka', 'Hasitha', 'Amali'];
        $lastNames = ['Rathnayake', 'Kumari', 'Jayawardena', 'Siriwardena', 'Madushanka', 'Perera', 'Dissanayake', 'Weerasinghe', 'Abeysekara', 'Senanayake', 'Karunaratne', 'Wijesinghe', 'Ranasinghe', 'Amarasinghe', 'Liyanage'];
        $commercial = ['Hardware Store', 'Cooperative Shop', 'Tea Cafe', 'Groceries', 'Fuel Mart', 'Communications', 'Bakery', 'Pharmacy', 'Textile Shop', 'Spare Parts'];
        $industrial = ['Workshop', 'Processing Unit', 'Packing House', 'Pump House', 'Factory Block', 'Boiler House', 'Dry Store', 'Machine Shed'];

        $customers = [];
        $bar = $this->command?->getOutput()?->createProgressBar($this->customerCount);
        $bar?->start();

        for ($i = 1; $i <= $this->customerCount; $i++) {
            $division = $divisions[($i - 1) % $divisions->count()];
            $bucket = $i % 10;
            if ($bucket < 7) {
                $typeName = 'residential';
                $title = $titles[($i - 1) % 4];
                $first = $firstNames[($i - 1) % count($firstNames)];
                $last = $lastNames[($i - 1) % count($lastNames)] . $i;
                $staffType = ['STAFF', 'NON STAFF', 'MANAGEMENT', null][$i % 4];
            } elseif ($bucket < 9) {
                $typeName = 'commercial';
                $title = 'M/s';
                $first = $commercial[($i - 1) % count($commercial)];
                $last = 'Outlet ' . $i;
                $staffType = null;
            } else {
                $typeName = 'industrial';
                $title = 'M/s';
                $first = $industrial[($i - 1) % count($industrial)];
                $last = 'Unit ' . $i;
                $staffType = null;
            }

            $type = $types->firstWhere('name', $typeName) ?? $types->first();
            $guarantor = $guarantors[($i - 1) % count($guarantors)];

            $customers[] = Customer::create([
                'title' => $title,
                'first_name' => $first,
                'last_name' => $last,
                'email' => 'customer' . $i . '@example.com',
                'phone' => '07' . str_pad((string) (72000000 + $i), 8, '0', STR_PAD_LEFT),
                'nic' => sprintf('%d%07dV', 75 + ($i % 20), 3000000 + $i),
                'epf_number' => $staffType ? 'EPF' . str_pad((string) $i, 5, '0', STR_PAD_LEFT) : null,
                'address' => 'Line ' . (($i % 40) + 1) . ', ' . $division->name . ', Dunsinane Estate',
                'city' => 'Nuwara Eliya',
                'postal_code' => '22200',
                'status' => ($i % 40 === 0) ? 'suspended' : 'active',
                'customer_type' => $type->name,
                'customer_type_id' => $type->id,
                'division_id' => $division->id,
                'staff_type' => $staffType,
                'guarantor_id' => $guarantor->id,
                'connection_date' => Carbon::now()->subMonths(12 + ($i % 24))->toDateString(),
                'deposit_amount' => $typeName === 'industrial' ? 15000 : ($typeName === 'commercial' ? 5000 : 2000),
                'billing_day' => 20,
                'auto_billing_enabled' => true,
                'notes' => 'Large sample seeded customer #' . $i,
            ]);

            $bar?->advance();
        }

        $bar?->finish();
        $this->command?->newLine();

        return $customers;
    }

    private function seedMeters(array $customers): array
    {
        $meters = [];
        $brands = ['Kent', 'Sensus', 'Itron', 'Arad', 'Elster'];

        foreach ($customers as $i => $customer) {
            $n = $i + 1;
            $initial = 80 + ($n * 17);
            $meter = WaterMeter::create([
                'customer_id' => $customer->id,
                'meter_number' => 'WM-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
                'meter_brand' => $brands[$i % count($brands)],
                'meter_model' => 'DX-' . (100 + ($n % 50)),
                'meter_size' => $customer->customer_type === 'industrial' ? 25 : 15,
                'meter_type' => $customer->customer_type === 'industrial' ? 'digital' : 'mechanical',
                'installation_date' => $customer->connection_date,
                'last_maintenance_date' => Carbon::now()->subMonths(2 + ($n % 8))->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(4 + ($n % 8))->toDateString(),
                'initial_reading' => $initial,
                'current_reading' => $initial,
                'status' => $customer->status === 'suspended' ? 'inactive' : 'active',
                'multiplier' => 1,
                'location_notes' => 'Roadside meter box #' . $n,
                'latitude' => 6.9497 + ($n * 0.00021),
                'longitude' => 80.7891 + ($n * 0.00017),
                'address' => $customer->address,
                'notes' => 'Large sample meter',
            ]);

            $customer->update(['meter_number' => $meter->meter_number]);
            $meters[] = $meter;
        }

        return $meters;
    }

    private function seedReadingsAndBills(array $meters, $readers): void
    {
        $months = [];
        for ($m = $this->monthsOfHistory - 1; $m >= 0; $m--) {
            $months[] = Carbon::now()->subMonths($m)->startOfMonth();
        }

        $this->command?->info('Creating readings and bills...');
        $bar = $this->command?->getOutput()?->createProgressBar(count($meters));
        $bar?->start();

        foreach ($meters as $meterIndex => $meter) {
            $customer = $meter->customer;
            $previous = (float) $meter->initial_reading;

            foreach ($months as $monthIndex => $monthStart) {
                $consumption = match ($customer->customer_type) {
                    'industrial' => 70 + (($meterIndex + $monthIndex * 3) % 80),
                    'commercial' => 20 + (($meterIndex + $monthIndex * 2) % 45),
                    default => 6 + (($meterIndex + $monthIndex) % 22),
                };

                $current = $previous + $consumption;
                $readingDate = $monthStart->copy()->day(min(18, $monthStart->daysInMonth));
                $reader = $readers[$meterIndex % $readers->count()];
                $isLatestOpen = $monthIndex === count($months) - 1;

                $reading = MeterReading::create([
                    'water_meter_id' => $meter->id,
                    'reading_date' => $readingDate->toDateString(),
                    'current_reading' => $current,
                    'previous_reading' => $previous,
                    'consumption' => $consumption,
                    'reading_type' => 'actual',
                    'reader_name' => $reader->name,
                    'reader_id' => $reader->id,
                    'notes' => 'Large sample reading',
                    'is_billable' => true,
                    'status' => $isLatestOpen ? 'verified' : 'billed',
                    'meter_condition' => 'good',
                    'gps_latitude' => $meter->latitude,
                    'gps_longitude' => $meter->longitude,
                    'submitted_via' => 'web',
                ]);

                $meter->update(['current_reading' => $current]);

                if (!$isLatestOpen) {
                    $this->createBillForReading($customer, $meter, $reading, $monthStart, $meterIndex, $monthIndex);
                }

                $previous = $current;
            }

            $bar?->advance();
        }

        $bar?->finish();
        $this->command?->newLine();
    }

    private function createBillForReading(
        Customer $customer,
        WaterMeter $meter,
        MeterReading $reading,
        Carbon $monthStart,
        int $meterIndex,
        int $monthIndex
    ): void {
        $charges = Rate::calculateCharges(
            $customer->customer_type,
            (float) $reading->consumption,
            $reading->reading_date
        );

        $billDate = $monthStart->copy()->day(20);
        $dueDate = $billDate->copy()->addDays(15);
        $pattern = ($meterIndex + $monthIndex) % 5;
        $waterFixed = $charges['water_charges'] + $charges['fixed_charges'];
        $late = $pattern === 4 ? 150 : 0;
        $total = $waterFixed + $late;

        $paid = match ($pattern) {
            0, 1 => $total,
            2 => round($total * 0.5, 2),
            default => 0,
        };

        $status = match ($pattern) {
            0, 1 => 'paid',
            2 => 'sent',
            3 => 'sent',
            4 => 'overdue',
        };

        if ($pattern === 4) {
            $dueDate = Carbon::now()->subDays(7 + ($meterIndex % 12));
            $billDate = $dueDate->copy()->subDays(15);
        }

        Bill::withoutEvents(function () use (
            $customer,
            $meter,
            $reading,
            $billDate,
            $dueDate,
            $charges,
            $total,
            $paid,
            $status,
            $late,
            $monthStart
        ) {
            $year = $billDate->format('Y');
            $month = $billDate->format('m');
            $seqKey = $year . $month;
            $this->billSequence[$seqKey] = ($this->billSequence[$seqKey] ?? 0) + 1;
            $seq = $this->billSequence[$seqKey];

            Bill::create([
                'customer_id' => $customer->id,
                'water_meter_id' => $meter->id,
                'meter_reading_id' => $reading->id,
                'bill_number' => sprintf('WB%s%s%04d', $year, $month, $seq),
                'bill_date' => $billDate->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'billing_period_from' => $monthStart->toDateString(),
                'billing_period_to' => $monthStart->copy()->endOfMonth()->toDateString(),
                'previous_reading' => $reading->previous_reading,
                'current_reading' => $reading->current_reading,
                'consumption' => $reading->consumption,
                'water_charges' => $charges['water_charges'],
                'fixed_charges' => $charges['fixed_charges'],
                'service_charges' => 0,
                'late_fees' => $late,
                'taxes' => 0,
                'adjustments' => 0,
                'total_amount' => $total,
                'paid_amount' => $paid,
                'balance_amount' => $total - $paid,
                'status' => $status,
                'rate_breakdown' => $charges['breakdown'],
                'notes' => 'Large sample seeded bill',
                'sent_at' => in_array($status, ['sent', 'paid', 'overdue'], true) ? $billDate->copy()->addDay() : null,
                'paid_at' => $status === 'paid' ? $dueDate->copy()->subDays(2) : null,
            ]);
        });
    }

    private function seedSmsNotifications(): void
    {
        $this->command?->info('Creating SMS notifications...');
        $now = now();
        $inserted = 0;

        DB::table('bills')
            ->join('customers', 'customers.id', '=', 'bills.customer_id')
            ->select('bills.id as bill_id', 'bills.customer_id', 'bills.bill_number', 'bills.status', 'bills.balance_amount', 'customers.phone')
            ->orderBy('bills.id')
            ->chunk(400, function ($bills) use (&$inserted, $now) {
                $rows = [];
                foreach ($bills as $bill) {
                    if ($bill->bill_id % 2 !== 0) {
                        continue;
                    }

                    $type = match ($bill->status) {
                        'paid' => 'payment_confirmation',
                        'overdue' => 'overdue_alert',
                        default => 'new_bill',
                    };
                    $sent = $bill->bill_id % 8 !== 0;

                    $rows[] = [
                        'customer_id' => $bill->customer_id,
                        'bill_id' => $bill->bill_id,
                        'phone_number' => $bill->phone,
                        'message' => "AquaBill {$type} for {$bill->bill_number}. Balance LKR {$bill->balance_amount}.",
                        'type' => $type,
                        'status' => $sent ? 'sent' : 'failed',
                        'response_data' => json_encode(['sample' => true]),
                        'http_status' => $sent ? 200 : 500,
                        'retry_count' => $sent ? 0 : 1,
                        'sent_at' => $sent ? $now : null,
                        'scheduled_at' => $now,
                        'error_message' => $sent ? null : 'Sample gateway timeout',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($rows !== []) {
                    DB::table('sms_notifications')->insert($rows);
                    $inserted += count($rows);
                }
            });

        $this->command?->line("  SMS rows: {$inserted}");
    }

    private function seedActivityLogs(): void
    {
        $users = User::query()->get(['id', 'name', 'email', 'role']);
        if ($users->isEmpty()) {
            return;
        }

        $this->command?->info('Creating activity logs...');
        $actions = [
            ['action' => 'customer_created', 'module' => 'customers', 'description' => 'Created a sample customer'],
            ['action' => 'meter_reading_submitted', 'module' => 'meter_readings', 'description' => 'Submitted a field meter reading'],
            ['action' => 'bill_generated', 'module' => 'bills', 'description' => 'Generated a monthly water bill'],
            ['action' => 'payment_recorded', 'module' => 'payments', 'description' => 'Recorded a bill payment'],
            ['action' => 'sms_sent', 'module' => 'sms', 'description' => 'Sent a bill SMS notification'],
        ];

        $rows = [];
        $now = now();
        for ($i = 1; $i <= 800; $i++) {
            $user = $users[$i % $users->count()];
            $action = $actions[$i % count($actions)];
            $rows[] = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'action' => $action['action'],
                'subject_type' => null,
                'subject_id' => null,
                'subject_name' => 'Sample #' . $i,
                'description' => $action['description'] . " #{$i}",
                'module' => $action['module'],
                'method' => 'POST',
                'url' => '/sample',
                'route_name' => null,
                'old_values' => null,
                'new_values' => null,
                'properties' => json_encode(['sample' => true]),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'LargeSampleDataSeeder',
                'session_id' => null,
                'status' => 'success',
                'error_message' => null,
                'duration_ms' => 20 + ($i % 80),
                'created_at' => $now->copy()->subMinutes($i),
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('activity_logs')->insert($chunk);
        }
    }
}
