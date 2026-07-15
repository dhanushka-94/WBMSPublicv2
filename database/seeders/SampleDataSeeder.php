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

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        if (Customer::count() > 0) {
            $this->command?->warn('Customers already exist — skipping SampleDataSeeder to avoid duplicates.');
            return;
        }

        // Prevent Notify.lk calls while seeding bills
        Config::set('sms.bill_notifications.new_bill', false);
        Config::set('sms.bill_notifications.due_reminder', false);
        Config::set('sms.bill_notifications.overdue_alert', false);
        Config::set('sms.bill_notifications.payment_confirmation', false);
        Config::set('sms.bill_notifications.late_fee_notice', false);
        Config::set('sms.notifications.enabled', false);

        $this->command?->info('Seeding sample domain data...');

        $this->call([
            CustomerTypeSeeder::class,
            DivisionSeeder::class,
            RateSeeder::class,
        ]);

        $divisions = Division::all()->keyBy('custom_id');
        $types = CustomerType::all()->keyBy('name');
        $readers = User::where('role', 'meter_reader')->get();

        if ($readers->isEmpty()) {
            $this->command?->error('No meter readers found. Run SystemUsersSeeder first.');
            return;
        }

        DB::transaction(function () use ($divisions, $types, $readers) {
            $guarantors = $this->seedGuarantors();
            $customers = $this->seedCustomers($divisions, $types, $guarantors);
            $meters = $this->seedMeters($customers);
            $this->seedReadingsAndBills($meters, $readers);
        });

        $this->command?->info('Sample data seeded successfully.');
        $this->command?->table(
            ['Table', 'Count'],
            [
                ['Divisions', Division::count()],
                ['Customer types', CustomerType::count()],
                ['Rates', Rate::count()],
                ['Guarantors', Guarantor::count()],
                ['Customers', Customer::count()],
                ['Water meters', WaterMeter::count()],
                ['Meter readings', MeterReading::count()],
                ['Bills', Bill::count()],
            ]
        );
    }

    private function seedGuarantors(): array
    {
        $people = [
            ['Sunil', 'Perera', '771234567V', '0771234501', 'Father'],
            ['Kamala', 'Fernando', '801234568V', '0771234502', 'Mother'],
            ['Nimal', 'Silva', '751234569V', '0771234503', 'Brother'],
            ['Ruwan', 'Jayasinghe', '821234570V', '0771234504', 'Friend'],
            ['Chamari', 'Wickramasinghe', '851234571V', '0771234505', 'Sister'],
            ['Ajith', 'Bandara', '721234572V', '0771234506', 'Uncle'],
            ['Dilani', 'Gunasekara', '881234573V', '0771234507', 'Colleague'],
            ['Pradeep', 'Herath', '791234574V', '0771234508', 'Neighbor'],
        ];

        $guarantors = [];
        foreach ($people as $i => $person) {
            $guarantors[] = Guarantor::create([
                'guarantor_id' => 'G' . str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'first_name' => $person[0],
                'last_name' => $person[1],
                'nic' => $person[2],
                'phone' => $person[3],
                'email' => strtolower($person[0]) . '.' . strtolower($person[1]) . '@example.com',
                'address' => ($i + 1) . ' Estate Road, Dunsinane, Nuwara Eliya',
                'relationship' => $person[4],
                'is_active' => true,
            ]);
        }

        return $guarantors;
    }

    private function seedCustomers($divisions, $types, array $guarantors): array
    {
        $samples = [
            // first, last, title, type, division, staff_type, city
            ['Anura', 'Rathnayake', 'Mr', 'residential', 'UD', 'STAFF'],
            ['Seetha', 'Kumari', 'Mrs', 'residential', 'UD', 'NON STAFF'],
            ['Mahinda', 'Jayawardena', 'Mr', 'residential', 'LD', 'STAFF'],
            ['Nadeeka', 'Siriwardena', 'Ms', 'residential', 'LD', 'NON STAFF'],
            ['Kasun', 'Madushanka', 'Mr', 'residential', 'FL', 'STAFF'],
            ['Thilini', 'Perera', 'Mrs', 'residential', 'FL', 'NON STAFF'],
            ['Samantha', 'Dissanayake', 'Mr', 'residential', 'UD', 'MANAGEMENT'],
            ['Priyanka', 'Weerasinghe', 'Mrs', 'residential', 'LD', 'STAFF'],
            ['Roshan', 'Abeysekara', 'Mr', 'residential', 'FL', 'NON STAFF'],
            ['Malithi', 'Senanayake', 'Miss', 'residential', 'BZ', 'NON STAFF'],
            ['Lanka', 'Hardware Store', 'M/s', 'commercial', 'BZ', null],
            ['Estate', 'Cooperative Shop', 'M/s', 'commercial', 'BZ', null],
            ['Green', 'Tea Cafe', 'M/s', 'commercial', 'BZ', null],
            ['Hilltop', 'Groceries', 'M/s', 'commercial', 'LD', null],
            ['Dunsinane', 'Fuel Mart', 'M/s', 'commercial', 'FA', null],
            ['Pathum', 'Communications', 'Mr', 'commercial', 'BZ', null],
            ['Estate', 'Workshop', 'M/s', 'industrial', 'FA', null],
            ['Leaf', 'Processing Unit', 'M/s', 'industrial', 'FA', null],
            ['Packing', 'House A', 'M/s', 'industrial', 'FA', null],
            ['Irrigation', 'Pump House', 'M/s', 'industrial', 'UD', null],
            ['Gayan', 'Karunaratne', 'Mr', 'residential', 'UD', 'STAFF'],
            ['Ishara', 'Wijesinghe', 'Mrs', 'residential', 'LD', 'NON STAFF'],
            ['Nuwan', 'Ranasinghe', 'Mr', 'residential', 'FL', 'STAFF'],
            ['Sanduni', 'Amarasinghe', 'Ms', 'residential', 'UD', 'NON STAFF'],
            ['Chathura', 'Liyanage', 'Mr', 'residential', 'LD', 'STAFF'],
        ];

        $customers = [];
        foreach ($samples as $i => $row) {
            [$first, $last, $title, $typeName, $divCode, $staffType] = $row;
            $division = $divisions[$divCode];
            $type = $types[$typeName];
            $guarantor = $guarantors[$i % count($guarantors)];

            $year = 75 + ($i % 20);
            $nic = sprintf('%d%07dV', $year, 1000000 + $i);

            $customers[] = Customer::create([
                'title' => $title,
                'first_name' => $first,
                'last_name' => $last,
                'email' => strtolower(preg_replace('/\s+/', '', $first)) . $i . '@example.com',
                'phone' => '07' . str_pad((string) (71234500 + $i), 8, '0', STR_PAD_LEFT),
                'nic' => $nic,
                'epf_number' => $staffType ? 'EPF' . str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT) : null,
                'address' => 'Line ' . (($i % 12) + 1) . ', ' . $division->name . ', Dunsinane Estate',
                'city' => 'Nuwara Eliya',
                'postal_code' => '22200',
                'status' => $i === 9 ? 'suspended' : 'active',
                'customer_type' => $typeName,
                'customer_type_id' => $type->id,
                'division_id' => $division->id,
                'staff_type' => $staffType,
                'guarantor_id' => $guarantor->id,
                'connection_date' => Carbon::now()->subMonths(18 + ($i % 12))->toDateString(),
                'deposit_amount' => $typeName === 'industrial' ? 15000 : ($typeName === 'commercial' ? 5000 : 2000),
                'billing_day' => 20,
                'auto_billing_enabled' => true,
                'notes' => 'Sample seeded customer',
            ]);
        }

        return $customers;
    }

    private function seedMeters(array $customers): array
    {
        $meters = [];
        $brands = ['Kent', 'Sensus', 'Itron', 'Arad'];

        foreach ($customers as $i => $customer) {
            $initial = 100 + ($i * 37);
            $meters[] = WaterMeter::create([
                'customer_id' => $customer->id,
                'meter_number' => 'WM-' . str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT),
                'meter_brand' => $brands[$i % count($brands)],
                'meter_model' => 'DX-' . (100 + $i),
                'meter_size' => $customer->customer_type === 'industrial' ? 25 : 15,
                'meter_type' => $customer->customer_type === 'industrial' ? 'digital' : 'mechanical',
                'installation_date' => $customer->connection_date,
                'last_maintenance_date' => Carbon::now()->subMonths(3)->toDateString(),
                'next_maintenance_date' => Carbon::now()->addMonths(9)->toDateString(),
                'initial_reading' => $initial,
                'current_reading' => $initial,
                'status' => $customer->status === 'suspended' ? 'inactive' : 'active',
                'multiplier' => 1,
                'location_notes' => 'Near main entrance / roadside box',
                'latitude' => 6.9497 + ($i * 0.0008),
                'longitude' => 80.7891 + ($i * 0.0006),
                'address' => $customer->address,
                'notes' => 'Sample meter',
            ]);

            $customer->update(['meter_number' => $meters[$i]->meter_number]);
        }

        return $meters;
    }

    private function seedReadingsAndBills(array $meters, $readers): void
    {
        // Last 4 calendar months of readings/bills
        $months = [
            Carbon::now()->subMonths(3)->startOfMonth(),
            Carbon::now()->subMonths(2)->startOfMonth(),
            Carbon::now()->subMonths(1)->startOfMonth(),
            Carbon::now()->startOfMonth(),
        ];

        foreach ($meters as $meterIndex => $meter) {
            $customer = $meter->customer;
            $previous = (float) $meter->initial_reading;

            foreach ($months as $monthIndex => $monthStart) {
                $consumption = match ($customer->customer_type) {
                    'industrial' => 80 + (($meterIndex + $monthIndex) % 40),
                    'commercial' => 25 + (($meterIndex + $monthIndex) % 30),
                    default => 8 + (($meterIndex + $monthIndex) % 18),
                };

                $current = $previous + $consumption;
                $readingDate = $monthStart->copy()->day(min(18, $monthStart->daysInMonth));
                $reader = $readers[$meterIndex % $readers->count()];

                $isLatestOpen = $monthIndex === count($months) - 1;
                $status = $isLatestOpen ? 'verified' : 'billed';

                $reading = MeterReading::create([
                    'water_meter_id' => $meter->id,
                    'reading_date' => $readingDate->toDateString(),
                    'current_reading' => $current,
                    'previous_reading' => $previous,
                    'consumption' => $consumption,
                    'reading_type' => 'actual',
                    'reader_name' => $reader->name,
                    'reader_id' => $reader->id,
                    'notes' => 'Sample reading',
                    'is_billable' => true,
                    'status' => $status,
                    'meter_condition' => 'good',
                    'gps_latitude' => $meter->latitude,
                    'gps_longitude' => $meter->longitude,
                    'submitted_via' => 'web',
                ]);

                $meter->update(['current_reading' => $current]);

                // Bills for older months; leave current month reading unverified-as-billed for demo
                if (!$isLatestOpen) {
                    $this->createBillForReading($customer, $meter, $reading, $monthStart, $meterIndex, $monthIndex);
                }

                $previous = $current;
            }
        }
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

        $pattern = ($meterIndex + $monthIndex) % 4;
        // 0 paid, 1 partial, 2 sent unpaid, 3 overdue unpaid
        $total = $charges['water_charges'] + $charges['fixed_charges'];
        $paid = match ($pattern) {
            0 => $total,
            1 => round($total * 0.4, 2),
            default => 0,
        };

        $status = match ($pattern) {
            0 => 'paid',
            1 => 'sent',
            2 => 'sent',
            3 => 'overdue',
        };

        if ($pattern === 3) {
            $dueDate = Carbon::now()->subDays(10 + ($meterIndex % 5));
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
            $monthStart
        ) {
            $year = $billDate->format('Y');
            $month = $billDate->format('m');
            $seq = Bill::where('bill_number', 'like', "WB{$year}{$month}%")->count() + 1;

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
                'late_fees' => $status === 'overdue' ? 100 : 0,
                'taxes' => 0,
                'adjustments' => 0,
                'total_amount' => $total + ($status === 'overdue' ? 100 : 0),
                'paid_amount' => $paid,
                'balance_amount' => ($total + ($status === 'overdue' ? 100 : 0)) - $paid,
                'status' => $status,
                'rate_breakdown' => $charges['breakdown'],
                'notes' => 'Sample seeded bill',
                'sent_at' => in_array($status, ['sent', 'paid', 'overdue'], true) ? $billDate->copy()->addDay() : null,
                'paid_at' => $status === 'paid' ? $dueDate->copy()->subDays(2) : null,
            ]);
        });
    }
}
