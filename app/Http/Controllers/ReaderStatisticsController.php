<?php

namespace App\Http\Controllers;

use App\Models\MeterReading;
use App\Models\User;
use App\Support\TablePage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReaderStatisticsController extends Controller
{
    public function index(Request $request): View
    {
        $month = $request->input('month', now()->format('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', (string) $month)) {
            $month = now()->format('Y-m');
        }

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $readers = User::query()
            ->where('role', 'meter_reader')
            ->with('routeDivisions:id,name')
            ->orderBy('name')
            ->get();

        $grouped = DB::table('meter_readings')
            ->whereBetween('reading_date', [$start->toDateString(), $end->toDateString()])
            ->whereIn('reader_id', $readers->pluck('id')->all() ?: [0])
            ->groupBy('reader_id')
            ->selectRaw('reader_id')
            ->selectRaw('COUNT(*) as readings')
            ->selectRaw("SUM(CASE WHEN submitted_via = 'mobile_app' THEN 1 ELSE 0 END) as phone")
            ->selectRaw('SUM(consumption) as consumption')
            ->selectRaw('COUNT(DISTINCT water_meter_id) as meters')
            ->selectRaw("SUM(CASE WHEN status = 'billed' THEN 1 ELSE 0 END) as billed")
            ->selectRaw("SUM(CASE WHEN meter_condition IN ('damaged', 'broken', 'needs_repair') THEN 1 ELSE 0 END) as faults")
            ->get()
            ->keyBy('reader_id');

        $summaries = $readers->map(function (User $reader) use ($grouped, $start, $end) {
            $row = $grouped->get($reader->id);
            $assignedIds = $reader->assignedRouteCustomers()->pluck('customers.id');
            $covered = $assignedIds->isEmpty()
                ? 0
                : (int) DB::table('meter_readings')
                    ->join('water_meters', 'water_meters.id', '=', 'meter_readings.water_meter_id')
                    ->where('meter_readings.reader_id', $reader->id)
                    ->whereBetween('meter_readings.reading_date', [$start->toDateString(), $end->toDateString()])
                    ->whereIn('water_meters.customer_id', $assignedIds)
                    ->distinct()
                    ->count('water_meters.customer_id');

            $readings = (int) ($row->readings ?? 0);
            $assigned = $assignedIds->count();

            return [
                'reader' => $reader,
                'divisions' => $reader->routeDivisions->pluck('name'),
                'readings' => $readings,
                'phone' => (int) ($row->phone ?? 0),
                'office' => $readings - (int) ($row->phone ?? 0),
                'meters' => (int) ($row->meters ?? 0),
                'consumption' => (float) ($row->consumption ?? 0),
                'billed' => (int) ($row->billed ?? 0),
                'faults' => (int) ($row->faults ?? 0),
                'assigned' => $assigned,
                'covered' => $covered,
                'coverage' => $assigned > 0 ? (int) round(($covered / $assigned) * 100) : 0,
            ];
        });

        $selected = null;
        $days = collect();
        $readings = null;
        if ($request->filled('reader')) {
            $selected = $readers->firstWhere('id', (int) $request->input('reader'));
            abort_unless($selected, 404);

            $days = DB::table('meter_readings')
                ->where('reader_id', $selected->id)
                ->whereBetween('reading_date', [$start->toDateString(), $end->toDateString()])
                ->groupBy('reading_date')
                ->orderBy('reading_date')
                ->selectRaw('reading_date')
                ->selectRaw('COUNT(*) as readings')
                ->selectRaw("SUM(CASE WHEN submitted_via = 'mobile_app' THEN 1 ELSE 0 END) as phone")
                ->selectRaw('SUM(consumption) as consumption')
                ->get();

            $readings = MeterReading::query()
                ->with(['waterMeter.customer', 'bill'])
                ->where('reader_id', $selected->id)
                ->whereBetween('reading_date', [$start->toDateString(), $end->toDateString()])
                ->orderByDesc('reading_date')
                ->orderByDesc('id')
                ->paginate(25)
                ->withQueryString();
        }

        $unassigned = (int) DB::table('meter_readings')
            ->whereBetween('reading_date', [$start->toDateString(), $end->toDateString()])
            ->where(function ($query) use ($readers) {
                $query->whereNull('reader_id');
                if ($readers->isNotEmpty()) {
                    $query->orWhereNotIn('reader_id', $readers->pluck('id'));
                }
            })
            ->count();

        $totals = [
            'readings' => $summaries->sum('readings'),
            'phone' => $summaries->sum('phone'),
            'office' => $summaries->sum('office'),
            'consumption' => $summaries->sum('consumption'),
            'billed' => $summaries->sum('billed'),
            'faults' => $summaries->sum('faults'),
            'assigned' => $summaries->sum('assigned'),
            'covered' => $summaries->sum('covered'),
        ];

        return view('reports.reader-statistics', [
            'month' => $month,
            'label' => $start->format('F Y'),
            'summaries' => TablePage::collection($summaries),
            'selected' => $selected,
            'days' => $days,
            'dayPeak' => max(1, (int) $days->max('readings')),
            'readings' => $readings,
            'unassigned' => $unassigned,
            'totals' => $totals,
        ]);
    }
}
