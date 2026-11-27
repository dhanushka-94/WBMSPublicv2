<?php

namespace App\Http\Controllers;

use App\Models\SmsNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SmsUsageController extends Controller
{
    private const TYPES = [
        'new_bill' => 'New Bill',
        'due_reminder' => 'Due Reminder',
        'overdue_alert' => 'Overdue Alert',
        'payment_confirmation' => 'Payment Confirmation',
        'late_fee_notice' => 'Late Fee Notice',
        'service_disconnection' => 'Service Disconnection',
        'meter_reading_reminder' => 'Meter Reading Reminder',
        'maintenance_notice' => 'Maintenance Notice',
        'custom' => 'Custom SMS',
        'reconnection' => 'Reconnection Notice',
    ];

    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $rate = $this->currentRate();

        $totals = $this->aggregate($this->query($filters), $rate)->first();

        $periodExpr = $filters['group'] === 'month'
            ? "DATE_FORMAT(created_at, '%Y-%m')"
            : 'DATE(created_at)';

        $byPeriod = $this->aggregate($this->query($filters), $rate)
            ->selectRaw("{$periodExpr} as label")
            ->groupBy('label')->orderBy('label', 'desc')->get();

        $byType = $this->aggregate($this->query($filters), $rate)
            ->addSelect('type as label')
            ->groupBy('type')->orderByDesc('cost')->get()
            ->map(function ($row) {
                $row->label = self::TYPES[$row->label] ?? ucfirst(str_replace('_', ' ', (string) $row->label));

                return $row;
            });

        $bySource = $this->aggregate($this->query($filters), $rate)
            ->addSelect('source as label')
            ->groupBy('source')->orderByDesc('cost')->get();

        $byStatus = SmsNotification::query()
            ->tap(fn ($q) => $this->applyFilters($q, $filters, false))
            ->select('status', DB::raw('COUNT(*) as messages'))
            ->groupBy('status')->pluck('messages', 'status');

        return view('sms.usage', [
            'filters' => $filters,
            'rate' => $rate,
            'totals' => $totals,
            'byPeriod' => $byPeriod,
            'byType' => $byType,
            'bySource' => $bySource,
            'byStatus' => $byStatus,
            'types' => self::TYPES,
            'sources' => SmsNotification::query()->distinct()->orderBy('source')->pluck('source')->filter()->values(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $rate = $this->currentRate();
        $query = $this->query($filters)->with('customer')->orderBy('created_at');

        $callback = function () use ($query, $rate) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Date/Time', 'Customer', 'Account', 'Phone', 'Type', 'Source', 'Status', 'Segments', 'Rate (LKR)', 'Cost (LKR)']);

            $query->chunk(500, function ($rows) use ($out, $rate) {
                foreach ($rows as $row) {
                    $segments = max(1, (int) $row->segments);
                    $unit = $row->cost_per_segment !== null ? (float) $row->cost_per_segment : $rate;
                    $charged = $row->status === 'sent';
                    fputcsv($out, [
                        $row->created_at->format('Y-m-d H:i:s'),
                        $row->customer?->full_name ?? '',
                        $row->customer?->account_number ?? '',
                        $row->phone_number,
                        self::TYPES[$row->type] ?? $row->type,
                        $row->source,
                        $row->status,
                        $segments,
                        number_format($unit, 4, '.', ''),
                        number_format($charged ? $segments * $unit : 0, 2, '.', ''),
                    ]);
                }
            });

            fclose($out);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="sms_usage_'.now()->format('Y-m-d_His').'.csv"',
        ]);
    }

    private function currentRate(): float
    {
        return (float) config('sms.cost_per_segment', 0);
    }

    private function filters(Request $request): array
    {
        $data = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'status' => 'nullable|string|in:all,sent,failed,pending',
            'type' => 'nullable|string|max:50',
            'source' => 'nullable|string|max:20',
            'group' => 'nullable|in:day,month',
        ]);

        $hasRange = $request->has('date_from') || $request->has('date_to');

        return [
            'date_from' => $data['date_from'] ?? ($hasRange ? null : Carbon::now()->startOfMonth()->toDateString()),
            'date_to' => $data['date_to'] ?? ($hasRange ? null : Carbon::now()->toDateString()),
            'status' => $data['status'] ?? 'sent',
            'type' => $data['type'] ?? null,
            'source' => $data['source'] ?? null,
            'group' => $data['group'] ?? 'day',
        ];
    }

    private function applyFilters(Builder $query, array $filters, bool $withStatus = true): Builder
    {
        return $query
            ->when($filters['date_from'], fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'], fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($filters['type'], fn ($q, $v) => $q->where('type', $v))
            ->when($filters['source'], fn ($q, $v) => $q->where('source', $v))
            ->when($withStatus && $filters['status'] !== 'all', fn ($q) => $q->where('status', $filters['status']));
    }

    private function query(array $filters): Builder
    {
        return $this->applyFilters(SmsNotification::query(), $filters);
    }

    /**
     * Message, segment and cost totals. Only sent messages are charged.
     * Older rows without a stored rate use the current .env rate.
     */
    private function aggregate(Builder $query, float $rate): Builder
    {
        $segments = 'GREATEST(COALESCE(segments, 1), 1)';

        return $query->selectRaw(
            "COUNT(*) as messages,
            COALESCE(SUM({$segments}), 0) as segments,
            COALESCE(SUM(CASE WHEN status = 'sent' THEN {$segments} * COALESCE(cost_per_segment, ?) ELSE 0 END), 0) as cost",
            [$rate]
        );
    }
}
