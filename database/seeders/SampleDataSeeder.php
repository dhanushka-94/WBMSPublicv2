<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Bill;
use App\Models\ConnectionEvent;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Division;
use App\Models\Guarantor;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\Rate;
use App\Models\SmsNotification;
use App\Models\SystemConfiguration;
use App\Models\User;
use App\Models\WaterMeter;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    private array $units = [];

    public function run(): void
    {
        if (Customer::query()->exists()) {
            $this->command?->warn('Sample customers already exist, so this seeder did not add another set.');

            return;
        }

        $this->settings();
        $types = $this->customerTypes();
        $divisions = $this->divisions();
        $this->tariff();
        $guarantors = $this->guarantors();
        $customers = $this->customers($types, $divisions, $guarantors);
        $meters = $this->meters($customers);
        $readings = $this->readings($meters);
        $bills = $this->bills($customers, $meters, $readings);
        $this->payments($bills);
        $this->connectionEvents($customers);
        $this->messages($customers, $bills);
        $this->activity($customers, $bills);
        $this->routes($divisions, $customers);

        foreach ($meters as $meter) {
            $meter->regenerateQrCode();
        }
    }

    private function settings(): void
    {
        $rows = [
            ['system_enabled', '1', 'boolean', 'Office system is open for sign-in'],
            ['system_disabled_at', null, 'datetime', 'When the office system was last closed'],
            ['disable_reason', null, 'string', 'Reason shown when the office system is closed'],
            ['default_billing_day', '20', 'integer', 'Default billing day of the month for a new customer'],
            ['auto_billing_enabled_default', '1', 'boolean', 'New customers are included in monthly bill generation'],
            ['billing_cycle_type', 'monthly', 'string', 'How often bills are generated'],
            ['scheme_name', 'Dunsinane Water Supply Scheme', 'string', 'Name printed on the bill'],
            ['currency', 'LKR', 'string', 'Currency used on bills and receipts'],
            ['office_phone', '0522223344', 'string', 'Scheme office telephone'],
            ['bill_due_days', '30', 'integer', 'Days after the bill date before payment is due'],
        ];

        foreach ($rows as [$key, $value, $type, $description]) {
            SystemConfiguration::set($key, $value, $type, $description);
        }
    }

    private function customerTypes(): array
    {
        $rows = [
            ['Residential', 'RES', 'Houses and estate line rooms'],
            ['Commercial', 'COM', 'Shops, offices, and other businesses'],
            ['Industrial', 'IND', 'Factories and processing sites'],
        ];

        return array_map(fn ($row) => CustomerType::create([
            'name' => $row[0],
            'custom_id' => $row[1],
            'description' => $row[2],
            'is_active' => true,
        ]), $rows);
    }

    private function divisions(): array
    {
        $names = [
            ['Dunsinane', 'DUN', 'Main Dunsinane scheme area'],
            ['Dickoya', 'DCK', 'Dickoya line'],
            ['Norwood', 'NOR', 'Norwood division'],
            ['Maskeliya', 'MSK', 'Maskeliya division'],
            ['Upcot', 'UPC', 'Upcot division'],
            ['Bogawantalawa', 'BOG', 'Bogawantalawa division'],
            ['Hatton', 'HAT', 'Hatton town supply'],
            ['Kotagala', 'KOT', 'Kotagala division'],
            ['Lindula', 'LIN', 'Lindula division'],
            ['Talawakelle', 'TAL', 'Talawakelle division'],
        ];

        return array_map(fn ($row) => Division::create([
            'name' => $row[0],
            'custom_id' => $row[1],
            'description' => $row[2],
            'is_active' => true,
        ]), $names);
    }

    private function tariff(): void
    {
        $slabs = [
            ['Fixed charge', 0, 0, 0, 200, 'Charged on every bill, including a zero-consumption month'],
            ['0 to 5 units', 0, 5, 0, 0, 'First five units'],
            ['6 to 10 units', 6, 10, 12, 0, 'Next five units at 12 LKR'],
            ['11 to 15 units', 11, 15, 18, 0, 'Next five units at 18 LKR'],
            ['16 to 20 units', 16, 20, 25, 0, 'Next five units at 25 LKR'],
            ['21 to 25 units', 21, 25, 35, 0, 'Next five units at 35 LKR'],
            ['26 units and above', 26, 100000, 40, 0, 'Every unit above 25 at 40 LKR'],
        ];

        foreach (['residential', 'commercial', 'industrial'] as $type) {
            foreach ($slabs as $slab) {
                Rate::create([
                    'name' => ucfirst($type).' '.$slab[0],
                    'customer_type' => $type,
                    'tier_from' => $slab[1],
                    'tier_to' => $slab[2],
                    'rate_per_unit' => $slab[3],
                    'fixed_charge' => $slab[4],
                    'is_active' => true,
                    'effective_from' => '2026-01-01',
                    'effective_to' => null,
                    'description' => $slab[5],
                ]);
            }
        }
    }

    private function guarantors(): array
    {
        $people = [
            ['Kamal', 'Perera', '196512345678', '0772000001', 'kamal.perera@example.com', '12 Temple Road, Hatton', 'Father'],
            ['Nirmala', 'Silva', '197023456789', '0772000002', 'nirmala.silva@example.com', '45 Church Street, Dickoya', 'Mother'],
            ['Ravi', 'Fernando', '198134567890', '0772000003', 'ravi.fernando@example.com', '8 Station Road, Norwood', 'Spouse'],
            ['Kumari', 'Jayasinghe', '198845678901', '0772000004', 'kumari.j@example.com', '3 Market Lane, Maskeliya', 'Sister'],
            ['Sunil', 'Bandara', '197556789012', '0772000005', 'sunil.bandara@example.com', '21 Estate Road, Upcot', 'Brother'],
            ['Malini', 'Wijesinghe', '196967890123', '0772000006', 'malini.w@example.com', '6 Lake View, Bogawantalawa', 'Aunt'],
            ['Ajith', 'Rajapaksa', '198078901234', '0772000007', 'ajith.r@example.com', '14 Hill Street, Hatton', 'Uncle'],
            ['Deepa', 'Gunawardena', '199189012345', '0772000008', 'deepa.g@example.com', '9 Tea Road, Kotagala', 'Spouse'],
            ['Pradeep', 'Abeysekara', '197290123456', '0772000009', 'pradeep.a@example.com', '2 School Lane, Lindula', 'Father'],
            ['Chandani', 'Herath', '198301234567', '0772000010', 'chandani.h@example.com', '17 River Road, Talawakelle', 'Mother'],
        ];

        $rows = [];
        foreach ($people as $index => $person) {
            $rows[] = Guarantor::create([
                'guarantor_id' => 'G'.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT),
                'first_name' => $person[0],
                'last_name' => $person[1],
                'nic' => $person[2],
                'phone' => $person[3],
                'email' => $person[4],
                'address' => $person[5],
                'relationship' => $person[6],
                'is_active' => true,
            ]);
        }

        return $rows;
    }

    private function customers(array $types, array $divisions, array $guarantors): array
    {
        $people = [
            ['Mr', 'Nimal', 'Rajapaksha', 'active', 'residential', 'STAFF', 26],
            ['Mrs', 'Seetha', 'Weerasinghe', 'active', 'residential', 'NON STAFF', 10],
            ['Mr', 'Fazal', 'Mohamed', 'active', 'commercial', 'NON STAFF', 40],
            ['Ms', 'Anusha', 'Dias', 'active', 'industrial', 'MANAGEMENT', 15],
            ['Mr', 'Suresh', 'Kannan', 'active', 'residential', 'STAFF', 0],
            ['Mrs', 'Priya', 'Nadarajah', 'inactive', 'residential', 'NON STAFF', 8],
            ['Mr', 'Lakmal', 'Ekanayake', 'suspended', 'commercial', 'STAFF', 20],
            ['Mrs', 'Mala', 'Senanayake', 'disconnected', 'residential', 'NON STAFF', 12],
            ['Dr', 'Rohan', 'Peiris', 'active', 'commercial', 'MANAGEMENT', 18],
            ['Mr', 'Tharindu', 'Alwis', 'active', 'residential', 'STAFF', 22],
        ];

        $rows = [];
        foreach ($people as $index => $person) {
            $number = $index + 1;
            $type = $types[match ($person[4]) {
                'commercial' => 1,
                'industrial' => 2,
                default => 0,
            }];
            $division = $divisions[$index];
            $customer = new Customer([
                'account_number' => 'WB'.str_pad((string) $number, 5, '0', STR_PAD_LEFT),
                'reference_number' => 'CP/NE/DN/'.$division->custom_id.'/'.$type->custom_id.'/0001',
                'meter_number' => 'WM-'.str_pad((string) $number, 5, '0', STR_PAD_LEFT),
                'title' => $person[0],
                'first_name' => $person[1],
                'last_name' => $person[2],
                'email' => strtolower($person[1]).'.'.strtolower($person[2]).'@example.com',
                'phone' => '07710000'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'phone_two' => '07110000'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'nic' => '1990'.str_pad((string) (100000 + $number), 8, '0', STR_PAD_LEFT),
                'epf_number' => 'EPF'.str_pad((string) $number, 5, '0', STR_PAD_LEFT),
                'address' => $number.' Line Room, '.$division->name,
                'city' => $division->name,
                'postal_code' => (string) (22000 + $number),
                'status' => $person[3],
                'customer_type_id' => $type->id,
                'division_id' => $division->id,
                'staff_type' => $person[5],
                'guarantor_id' => $guarantors[$index]->id,
                'connection_date' => '2024-0'.(($index % 9) + 1).'-15',
                'deposit_amount' => 5000 + ($number * 250),
                'notes' => 'Sample connection '.$number.' for the Dunsinane scheme.',
                'billing_day' => 20,
                'next_billing_date' => '2026-11-20',
                'auto_billing_enabled' => $person[3] === 'active',
            ]);
            $customer->customer_type = $person[4];
            $customer->save();
            $this->units[$customer->id] = (int) $person[6];
            $rows[] = $customer;
        }

        return $rows;
    }

    private function meters(array $customers): array
    {
        $rows = [];
        foreach ($customers as $index => $customer) {
            $lat = 6.87000000 + ($index * 0.004);
            $lng = 80.60000000 + ($index * 0.003);
            $start = 1000 + ($index * 80);
            $rows[] = WaterMeter::create([
                'customer_id' => $customer->id,
                'meter_number' => $customer->meter_number,
                'meter_brand' => $index % 2 === 0 ? 'Itron' : 'Elster',
                'meter_model' => 'A'.(100 + $index),
                'meter_size' => 15,
                'meter_type' => $index === 3 ? 'digital' : 'mechanical',
                'installation_date' => $customer->connection_date,
                'last_maintenance_date' => '2026-06-10',
                'next_maintenance_date' => '2027-06-10',
                'initial_reading' => $start,
                'current_reading' => $start + 8 + $this->units[$customer->id],
                'status' => $index === 7 ? 'faulty' : 'active',
                'multiplier' => 1,
                'location_notes' => 'Outside the kitchen wall, '.$customer->city,
                'latitude' => $lat,
                'longitude' => $lng,
                'address' => $customer->address,
                'google_place_id' => 'sample-place-'.$customer->meter_number,
                'location_metadata' => ['source' => 'sample', 'accuracy_m' => 8],
                'notes' => 'Sample meter for '.$customer->first_name.' '.$customer->last_name,
            ]);
        }

        return $rows;
    }

    private function readings(array $meters): array
    {
        $reader = User::where('email', 'reader1@aquabill.olexto.com')->first();
        $latest = [];

        foreach ($meters as $index => $meter) {
            $start = (float) $meter->initial_reading;
            $units = $this->units[$meter->customer_id];
            $olderUnits = 8;
            $conditions = ['good', 'good', 'good', 'leaking', 'good', 'good', 'needs_repair', 'damaged', 'good', 'good'];

            MeterReading::create([
                'water_meter_id' => $meter->id,
                'reading_date' => '2026-08-18',
                'previous_reading' => $start,
                'current_reading' => $start + $olderUnits,
                'consumption' => $olderUnits,
                'reading_type' => 'actual',
                'reader_name' => $reader->name,
                'reader_id' => $reader->id,
                'notes' => 'August sample reading',
                'meter_condition' => 'good',
                'photo_path' => null,
                'gps_latitude' => $meter->latitude,
                'gps_longitude' => $meter->longitude,
                'submitted_via' => 'mobile_app',
                'offline_timestamp' => '2026-08-18 09:15:00',
                'is_billable' => true,
                'status' => 'billed',
            ]);

            $latest[] = MeterReading::create([
                'water_meter_id' => $meter->id,
                'reading_date' => '2026-09-18',
                'previous_reading' => $start + $olderUnits,
                'current_reading' => $start + $olderUnits + $units,
                'consumption' => $units,
                'reading_type' => $index === 5 ? 'estimated' : 'actual',
                'reader_name' => $reader->name,
                'reader_id' => $reader->id,
                'notes' => 'September sample reading',
                'meter_condition' => $conditions[$index],
                'photo_path' => null,
                'gps_latitude' => $meter->latitude,
                'gps_longitude' => $meter->longitude,
                'submitted_via' => $index % 2 === 0 ? 'mobile_app' : 'web',
                'offline_timestamp' => '2026-09-18 10:30:00',
                'is_billable' => true,
                'status' => 'billed',
            ]);
        }

        return $latest;
    }

    private function bills(array $customers, array $meters, array $readings): array
    {
        $plans = [
            ['paid', 0, 0, 0, 0, '2026-09-20', '2026-10-20', 'Paid in full at the counter'],
            ['paid', 50, 0, 0, 0, '2026-09-20', '2026-10-20', 'Paid by bank transfer'],
            ['partial', 0, 25, 0, 0, '2026-09-20', '2026-11-19', 'Part paid by cheque'],
            ['generated', 0, 0, 0, 0, '2026-09-20', '2026-11-19', 'Waiting for payment'],
            ['generated', 0, 0, 0, 0, '2026-09-20', '2026-11-19', 'Zero consumption, fixed charge only'],
            ['overdue', 0, 0, 100, 0, '2026-08-20', '2026-09-19', 'Not paid by the due date'],
            ['overdue', 0, 0, 0, -50, '2026-08-20', '2026-09-19', 'Suspended account, small credit adjustment'],
            ['sent', 0, 0, 0, 0, '2026-09-20', '2026-11-19', 'Bill handed to the customer'],
            ['draft', 75, 0, 0, 0, '2026-10-01', '2026-11-19', 'Draft still being checked'],
            ['cancelled', 0, 0, 0, 0, '2026-09-01', '2026-11-19', 'Cancelled after the connection was restored'],
        ];

        $bills = [];
        Bill::withoutEvents(function () use ($customers, $meters, $readings, $plans, &$bills) {
            foreach ($plans as $index => $plan) {
                $customer = $customers[$index];
                $units = $this->units[$customer->id];
                $charges = Rate::calculateCharges($customer->customer_type, $units, $plan[5]);
                $water = $charges['water_charges'];
                $fixed = $charges['fixed_charges'];
                $service = $plan[1];
                $taxes = $plan[2];
                $late = $plan[3];
                $adjustment = $plan[4];
                $total = $water + $fixed + $service + $taxes + $late + $adjustment;
                $paid = match ($plan[0]) {
                    'paid' => $total,
                    'partial' => round($total / 2, 2),
                    default => 0,
                };

                $bills[] = Bill::create([
                    'customer_id' => $customer->id,
                    'water_meter_id' => $meters[$index]->id,
                    'meter_reading_id' => $readings[$index]->id,
                    'bill_number' => 'WB202609'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'bill_date' => $plan[5],
                    'due_date' => $plan[6],
                    'billing_period_from' => '2026-08-19',
                    'billing_period_to' => '2026-09-18',
                    'previous_reading' => $readings[$index]->previous_reading,
                    'current_reading' => $readings[$index]->current_reading,
                    'consumption' => $units,
                    'water_charges' => $water,
                    'fixed_charges' => $fixed,
                    'service_charges' => $service,
                    'late_fees' => $late,
                    'taxes' => $taxes,
                    'adjustments' => $adjustment,
                    'total_amount' => $total,
                    'paid_amount' => $paid,
                    'balance_amount' => $total - $paid,
                    'status' => $plan[0] === 'partial' ? 'sent' : $plan[0],
                    'rate_breakdown' => $charges['breakdown'],
                    'notes' => $plan[7],
                    'sent_at' => in_array($plan[0], ['sent', 'paid', 'partial', 'overdue'], true) ? $plan[5].' 14:30:00' : null,
                    'paid_at' => $plan[0] === 'paid' ? '2026-10-05 11:15:00' : null,
                ]);
            }
        });

        return $bills;
    }

    private function payments(array $bills): void
    {
        $cashier = User::where('email', 'cashier@aquabill.olexto.com')->first();
        $methods = ['cash', 'bank_transfer', 'cheque', 'card'];
        $number = 1;

        foreach ($bills as $index => $bill) {
            if ($bill->paid_amount <= 0) {
                continue;
            }

            $half = round($bill->paid_amount / 2, 2);
            $parts = $index === 2 ? [$half, round($bill->paid_amount - $half, 2)] : [$bill->paid_amount];
            foreach ($parts as $partIndex => $amount) {
                Payment::create([
                    'bill_id' => $bill->id,
                    'customer_id' => $bill->customer_id,
                    'received_by' => $cashier->id,
                    'receipt_number' => 'RC202610'.str_pad((string) $number, 4, '0', STR_PAD_LEFT),
                    'amount' => $amount,
                    'payment_method' => $methods[$index % 4],
                    'paid_on' => $partIndex === 0 ? '2026-10-05' : '2026-10-12',
                    'reference' => 'SAMPLE-'.str_pad((string) $number, 4, '0', STR_PAD_LEFT),
                    'notes' => 'Sample receipt for '.$bill->bill_number,
                ]);
                $number++;
            }
        }
    }

    private function connectionEvents(array $customers): void
    {
        $officer = User::where('email', 'billing.officer@aquabill.olexto.com')->first();
        $events = [
            [7, 'disconnected', '2026-09-01', 'Non-payment for three months', 'Supply closed at the meter'],
            [9, 'disconnected', '2026-07-01', 'Requested by the customer', 'Away from the estate'],
            [9, 'reconnected', '2026-08-01', 'Customer returned', 'Deposit confirmed and supply opened'],
            [5, 'disconnected', '2026-05-10', 'Vacant line room', 'Marked inactive after the closure'],
            [5, 'reconnected', '2026-06-01', 'New occupant', 'Reading restarted'],
            [6, 'disconnected', '2026-09-15', 'Meter leak under investigation', 'Supply held until repair'],
            [1, 'disconnected', '2025-12-01', 'Internal pipe repair', 'Closed for one day'],
            [1, 'reconnected', '2025-12-02', 'Repair finished', 'Supply restored the next morning'],
            [3, 'disconnected', '2026-03-01', 'Factory shutdown', 'Industrial line closed'],
            [3, 'reconnected', '2026-04-01', 'Factory reopened', 'Meter checked before opening'],
        ];

        foreach ($events as $event) {
            ConnectionEvent::create([
                'customer_id' => $customers[$event[0]]->id,
                'user_id' => $officer->id,
                'action' => $event[1],
                'event_date' => $event[2],
                'reason' => $event[3],
                'notes' => $event[4],
            ]);
        }
    }

    private function messages(array $customers, array $bills): void
    {
        $types = [
            'new_bill',
            'due_reminder',
            'overdue_alert',
            'payment_confirmation',
            'late_fee_notice',
            'service_disconnection',
            'meter_reading_reminder',
            'maintenance_notice',
            'custom',
            'reconnection',
        ];
        $statuses = ['sent', 'sent', 'failed', 'sent', 'pending', 'sent', 'cancelled', 'sent', 'pending', 'sent'];

        foreach ($types as $index => $type) {
            SmsNotification::create([
                'customer_id' => $customers[$index]->id,
                'bill_id' => $bills[$index]->id,
                'phone_number' => '94'.ltrim($customers[$index]->phone, '0'),
                'message' => 'Sample '.$type.' message for '.$customers[$index]->account_number.'. This was not sent to a phone.',
                'type' => $type,
                'status' => $statuses[$index],
                'response_data' => ['sample' => true, 'provider' => 'none'],
                'http_status' => $statuses[$index] === 'failed' ? 500 : 200,
                'retry_count' => $statuses[$index] === 'failed' ? 1 : 0,
                'sent_at' => $statuses[$index] === 'sent' ? '2026-10-06 09:00:00' : null,
                'scheduled_at' => '2026-10-06 08:00:00',
                'error_message' => $statuses[$index] === 'failed' ? 'Sample failure. No message left the office.' : null,
            ]);
        }
    }

    private function activity(array $customers, array $bills): void
    {
        $admin = User::where('email', 'admin@aquabill.olexto.com')->first();
        $actions = [
            ['login', 'auth', 'Signed in to the office'],
            ['create', 'customers', 'Added a sample customer'],
            ['update', 'customers', 'Updated a customer address'],
            ['create', 'readings', 'Saved a meter reading'],
            ['create', 'bills', 'Generated a sample bill'],
            ['create', 'payments', 'Recorded a counter payment'],
            ['update', 'customers', 'Disconnected a connection'],
            ['update', 'customers', 'Reconnected a connection'],
            ['create', 'sms', 'Queued a sample SMS'],
            ['read', 'reports', 'Opened the daily collection report'],
        ];

        foreach ($actions as $index => $action) {
            ActivityLog::create([
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'user_email' => $admin->email,
                'user_role' => $admin->role,
                'action' => $action[0],
                'subject_type' => $index < 8 ? Customer::class : Bill::class,
                'subject_id' => $index < 8 ? $customers[$index]->id : $bills[min($index, 9)]->id,
                'subject_name' => $customers[$index]->account_number,
                'description' => $action[2],
                'module' => $action[1],
                'method' => 'POST',
                'url' => 'http://localhost:8000/'.$action[1],
                'route_name' => $action[1].'.store',
                'old_values' => ['status' => 'active'],
                'new_values' => ['status' => $customers[$index]->status],
                'properties' => ['sample' => true],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Sample seeder',
                'session_id' => 'sample-session-'.$index,
                'status' => 'success',
                'error_message' => null,
                'duration_ms' => 40 + $index,
            ]);
        }
    }

    private function routes(array $divisions, array $customers): void
    {
        $reader1 = User::where('email', 'reader1@aquabill.olexto.com')->first();
        $reader2 = User::where('email', 'reader2@aquabill.olexto.com')->first();
        $reader3 = User::where('email', 'reader3@aquabill.olexto.com')->first();

        $reader1->routeDivisions()->sync([
            $divisions[0]->id,
            $divisions[1]->id,
            $divisions[2]->id,
            $divisions[3]->id,
            $divisions[4]->id,
        ]);
        $reader2->routeCustomers()->sync([
            $customers[5]->id,
            $customers[6]->id,
            $customers[7]->id,
            $customers[8]->id,
            $customers[9]->id,
        ]);
        $reader3->routeDivisions()->sync([$divisions[6]->id, $divisions[7]->id]);
    }
}
