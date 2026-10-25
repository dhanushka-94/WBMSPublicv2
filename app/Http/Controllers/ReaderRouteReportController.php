<?php

namespace App\Http\Controllers;

use App\Models\MeterReading;
use App\Models\User;
use App\Support\TablePage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReaderRouteReportController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->filled('date')
            ? Carbon::parse($request->input('date'))->toDateString()
            : now()->toDateString();

        $list = in_array($request->input('list'), ['missed', 'completed', 'all'], true)
            ? $request->input('list')
            : 'missed';

        $readers = User::query()
            ->where('role', 'meter_reader')
            ->with('routeDivisions:id,name')
            ->orderBy('name')
            ->get();

        $summaries = $readers->map(fn (User $reader) => $this->summarize($reader, $date));

        $selected = null;
        $rows = null;
        if ($request->filled('reader')) {
            $selected = $readers->firstWhere('id', (int) $request->input('reader'));
            abort_unless($selected, 404);
            $rows = $this->detail($selected, $date, $list, $request);
        }

        $totals = [
            'assigned' => $summaries->sum('assigned'),
            'completed' => $summaries->sum('completed'),
            'missed' => $summaries->sum('missed'),
        ];

        return view('reports.reader-routes', [
            'date' => $date,
            'list' => $list,
            'summaries' => TablePage::collection($summaries, 'summary_page'),
            'selected' => $selected,
            'rows' => $rows,
            'totals' => $totals,
        ]);
    }

    private function summarize(User $reader, string $date): array
    {
        $assignedIds = $reader->assignedRouteCustomers()->pluck('id');
        $readings = $assignedIds->isEmpty()
            ? collect()
            : DB::table('meter_readings')
                ->join('water_meters', 'water_meters.id', '=', 'meter_readings.water_meter_id')
                ->whereDate('meter_readings.reading_date', $date)
                ->whereIn('water_meters.customer_id', $assignedIds)
                ->get(['water_meters.customer_id', 'meter_readings.reader_id']);

        $completed = $readings->pluck('customer_id')->unique();
        $byReader = $readings->where('reader_id', $reader->id)->pluck('customer_id')->unique();

        return [
            'reader' => $reader,
            'divisions' => $reader->routeDivisions->pluck('name'),
            'assigned' => $assignedIds->count(),
            'completed' => $completed->count(),
            'missed' => $assignedIds->count() - $completed->count(),
            'by_reader' => $byReader->count(),
        ];
    }

    private function detail(User $reader, string $date, string $list, Request $request): LengthAwarePaginator
    {
        $customers = $reader->assignedRouteCustomers()
            ->with('division:id,name')
            ->orderBy('account_number')
            ->get();

        $readings = $customers->isEmpty()
            ? collect()
            : MeterReading::query()
                ->select('meter_readings.*', 'water_meters.customer_id')
                ->join('water_meters', 'water_meters.id', '=', 'meter_readings.water_meter_id')
                ->whereDate('meter_readings.reading_date', $date)
                ->whereIn('water_meters.customer_id', $customers->pluck('id'))
                ->orderBy('meter_readings.created_at')
                ->get()
                ->keyBy('customer_id');

        $rows = $customers->map(function ($customer) use ($readings, $reader) {
            $reading = $readings->get($customer->id);

            return [
                'customer' => $customer,
                'status' => $reading ? 'completed' : 'missed',
                'reading' => $reading,
                'by_this_reader' => $reading && (int) $reading->reader_id === (int) $reader->id,
            ];
        });

        if ($list !== 'all') {
            $rows = $rows->where('status', $list)->values();
        }

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 25;

        return new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }
}
