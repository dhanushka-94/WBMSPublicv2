<?php

namespace App\Support;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * One row per customer for a chosen month: who they are, what they used and were billed
 * that month, and what they owe in total. The screen, the PDF and the Excel file all use
 * this class so they always show the same numbers.
 *
 *  - Usage: meter reading consumption with the reading dated inside the month.
 *  - This month's bill: bills whose billing period ends inside the month (cancelled ones ignored).
 *  - Total outstanding: balance on every unpaid bill (generated, sent or overdue), any month.
 */
class CustomerSummaryReport
{
    public const HEADINGS = [
        'Index Number',
        'Ref No',
        'EPF No',
        'Meter No',
        'Name',
        'Phone Number',
        'Division',
        'Customer type',
        'Male / Female',
        'Usage This Month (Units)',
        'This Month Bill Amount',
        'Total Outstanding',
    ];

    public const STAFF_LABELS = [
        'STAFF' => 'Staff',
        'NON STAFF' => 'Non staff',
        'MANAGEMENT' => 'Management',
    ];

    public const GENDER_LABELS = [
        'male' => 'Male',
        'female' => 'Female',
    ];

    /** Small credit line printed at the foot of the PDF and Excel exports. */
    public const DEVELOPER_CREDIT = 'Developed by olexto Digital Solutions (Pvt) Ltd';

    public readonly Carbon $monthStart;
    public readonly Carbon $monthEnd;

    /** @var array<string, string> */
    public readonly array $filters;

    public function __construct(Request $request)
    {
        $month = (string) $request->query('month', '');
        try {
            $start = preg_match('/^\d{4}-\d{2}$/', $month)
                ? Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfDay()
                : Carbon::now()->startOfMonth();
        } catch (\Throwable) {
            $start = Carbon::now()->startOfMonth();
        }

        $this->monthStart = $start->copy()->startOfMonth();
        $this->monthEnd = $start->copy()->endOfMonth();

        $this->filters = [
            'month' => $this->monthStart->format('Y-m'),
            'q' => trim((string) $request->query('q', '')),
            'division_id' => (string) $request->query('division_id', ''),
            'customer_type_id' => (string) $request->query('customer_type_id', ''),
            'staff_type' => (string) $request->query('staff_type', ''),
            'gender' => (string) $request->query('gender', ''),
            'status' => (string) $request->query('status', ''),
            'outstanding' => (string) $request->query('outstanding', ''),
            'usage' => (string) $request->query('usage', ''),
            'phone' => (string) $request->query('phone', ''),
        ];
    }

    public function monthLabel(): string
    {
        return $this->monthStart->format('F Y');
    }

    /** True when the list is the "customers with no phone number" view. */
    public function isMissingPhoneView(): bool
    {
        return $this->filters['phone'] === 'missing';
    }

    /** Report name for the page heading, PDF and Excel. */
    public function title(): string
    {
        return $this->isMissingPhoneView() ? 'Customers Without a Phone Number' : 'Customer Summary Report';
    }

    /** Title with the month, for the PDF and Excel headers. */
    public function titleWithMonth(): string
    {
        return $this->title().' - '.$this->monthLabel();
    }

    /** Names of the filters that are switched on, for the PDF and Excel header. */
    public function activeFilterText(): string
    {
        $parts = [];
        $f = $this->filters;

        if ($f['q'] !== '') {
            $parts[] = 'Search: '.$f['q'];
        }
        if ($f['division_id'] !== '') {
            $parts[] = 'Division: '.(\App\Models\Division::find($f['division_id'])?->name ?? $f['division_id']);
        }
        if ($f['customer_type_id'] !== '') {
            $parts[] = 'Customer type: '.(\App\Models\CustomerType::find($f['customer_type_id'])?->name ?? $f['customer_type_id']);
        }
        if ($f['staff_type'] !== '') {
            $parts[] = 'Group: '.(self::STAFF_LABELS[$f['staff_type']] ?? $f['staff_type']);
        }
        if ($f['gender'] !== '') {
            $parts[] = 'Gender: '.($f['gender'] === 'unset' ? 'Not set' : (self::GENDER_LABELS[$f['gender']] ?? $f['gender']));
        }
        if ($f['status'] !== '') {
            $parts[] = 'Status: '.ucfirst($f['status']);
        }
        if ($f['outstanding'] === 'yes') {
            $parts[] = 'With outstanding only';
        } elseif ($f['outstanding'] === 'no') {
            $parts[] = 'No outstanding only';
        }
        if ($f['phone'] === 'missing') {
            $parts[] = 'No phone number';
        } elseif ($f['phone'] === 'has') {
            $parts[] = 'Has a phone number';
        }
        if ($f['usage'] === 'yes') {
            $parts[] = 'Used water this month';
        } elseif ($f['usage'] === 'no') {
            $parts[] = 'No usage this month';
        }

        return $parts ? implode('  |  ', $parts) : 'All customers';
    }

    public function query(): Builder
    {
        $from = $this->monthStart->toDateString();
        $to = $this->monthEnd->toDateString();

        $query = Customer::query()
            ->with(['division:id,name', 'customerType:id,name'])
            ->select('customers.*')
            ->selectSub(
                DB::table('meter_readings as r')
                    ->join('water_meters as m', 'm.id', '=', 'r.water_meter_id')
                    ->whereColumn('m.customer_id', 'customers.id')
                    ->whereBetween('r.reading_date', [$from, $to])
                    ->selectRaw('COALESCE(SUM(r.consumption), 0)'),
                'usage_units'
            )
            ->selectSub(
                DB::table('bills as b')
                    ->whereColumn('b.customer_id', 'customers.id')
                    ->where('b.status', '!=', 'cancelled')
                    ->whereBetween('b.billing_period_to', [$from, $to])
                    ->selectRaw('COALESCE(SUM(b.total_amount), 0)'),
                'month_bill'
            )
            ->selectSub(
                DB::table('bills as o')
                    ->whereColumn('o.customer_id', 'customers.id')
                    ->whereIn('o.status', ['generated', 'sent', 'overdue'])
                    ->selectRaw('COALESCE(SUM(o.balance_amount), 0)'),
                'outstanding'
            );

        $f = $this->filters;

        if ($f['q'] !== '') {
            $term = '%'.addcslashes($f['q'], '%_\\').'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('customers.first_name', 'like', $term)
                    ->orWhere('customers.last_name', 'like', $term)
                    ->orWhereRaw("CONCAT_WS(' ', customers.first_name, customers.last_name) LIKE ?", [$term])
                    ->orWhere('customers.account_number', 'like', $term)
                    ->orWhere('customers.reference_number', 'like', $term)
                    ->orWhere('customers.epf_number', 'like', $term)
                    ->orWhere('customers.meter_number', 'like', $term)
                    ->orWhere('customers.phone', 'like', $term)
                    ->orWhere('customers.phone_two', 'like', $term);
            });
        }
        if ($f['division_id'] !== '') {
            $query->where('customers.division_id', (int) $f['division_id']);
        }
        if ($f['customer_type_id'] !== '') {
            $query->where('customers.customer_type_id', (int) $f['customer_type_id']);
        }
        if (isset(self::STAFF_LABELS[$f['staff_type']])) {
            $query->where('customers.staff_type', $f['staff_type']);
        }
        if (isset(self::GENDER_LABELS[$f['gender']])) {
            $query->where('customers.gender', $f['gender']);
        } elseif ($f['gender'] === 'unset') {
            $query->whereNull('customers.gender');
        }
        if (in_array($f['status'], ['active', 'inactive', 'suspended', 'disconnected'], true)) {
            $query->where('customers.status', $f['status']);
        }
        // A customer has no phone number when both phone fields are empty or blank.
        $noPhone = "TRIM(COALESCE(customers.phone, '')) = '' AND TRIM(COALESCE(customers.phone_two, '')) = ''";
        if ($f['phone'] === 'missing') {
            $query->whereRaw($noPhone);
        } elseif ($f['phone'] === 'has') {
            $query->whereRaw("NOT ({$noPhone})");
        }
        if ($f['outstanding'] === 'yes') {
            $query->having('outstanding', '>', 0);
        } elseif ($f['outstanding'] === 'no') {
            $query->having('outstanding', '<=', 0);
        }
        if ($f['usage'] === 'yes') {
            $query->having('usage_units', '>', 0);
        } elseif ($f['usage'] === 'no') {
            $query->having('usage_units', '<=', 0);
        }

        return $query->orderBy('customers.account_number');
    }

    /** Count and totals over everything the filters match, not just the page on screen. */
    public function totals(): array
    {
        $row = DB::query()
            ->fromSub($this->query()->toBase(), 't')
            ->selectRaw('COUNT(*) AS customers, COALESCE(SUM(usage_units), 0) AS usage_units, COALESCE(SUM(month_bill), 0) AS month_bill, COALESCE(SUM(outstanding), 0) AS outstanding')
            ->first();

        return [
            'customers' => (int) $row->customers,
            'usage_units' => (float) $row->usage_units,
            'month_bill' => (float) $row->month_bill,
            'outstanding' => (float) $row->outstanding,
        ];
    }

    /** One report row in the same order as HEADINGS (numbers stay numbers). */
    public static function row(Customer $customer, int $index): array
    {
        return [
            $index,
            $customer->reference_number ?: $customer->account_number,
            $customer->epf_number ?: '',
            $customer->meter_number ?: '',
            trim($customer->first_name.' '.$customer->last_name),
            $customer->phone ?: ($customer->phone_two ?: ''),
            $customer->division?->name ?? '',
            $customer->customerType?->name ?? '',
            self::GENDER_LABELS[$customer->gender] ?? '',
            round((float) $customer->usage_units, 2),
            round((float) $customer->month_bill, 2),
            round((float) $customer->outstanding, 2),
        ];
    }

    /** Every matching row, numbered from 1, for the exports. */
    public function allRows(): \Generator
    {
        $i = 0;
        // get() so division and customer type are loaded in two queries, not one per row.
        foreach ($this->query()->get() as $customer) {
            yield self::row($customer, ++$i);
        }
    }
}
