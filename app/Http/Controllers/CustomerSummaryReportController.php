<?php

namespace App\Http\Controllers;

use App\Models\CustomerType;
use App\Models\Division;
use App\Support\CustomerSummaryPdf;
use App\Support\CustomerSummaryReport;
use App\Support\SimpleXlsx;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerSummaryReportController extends Controller
{
    /** Report page with filters and a 25-row table. */
    public function index(Request $request): View
    {
        $report = new CustomerSummaryReport($request);

        $customers = $report->query()->paginate(25)->withQueryString();
        $firstIndex = ($customers->currentPage() - 1) * $customers->perPage();
        $rows = $customers->getCollection()
            ->values()
            ->map(fn ($customer, $i) => CustomerSummaryReport::row($customer, $firstIndex + $i + 1));

        return view('reports.customer-summary', [
            'report' => $report,
            'filters' => $report->filters,
            'headings' => CustomerSummaryReport::HEADINGS,
            'customers' => $customers,
            'rows' => $rows,
            'totals' => $report->totals(),
            'divisions' => Division::orderBy('name')->get(['id', 'name']),
            'customerTypes' => CustomerType::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /** PDF of everything the filters match (not just the page on screen). */
    public function pdf(Request $request): Response
    {
        $this->allowLongExport();
        $report = new CustomerSummaryReport($request);

        $content = CustomerSummaryPdf::render(
            $report,
            iterator_to_array($report->allRows(), false),
            $report->totals(),
            Auth::user()?->name
        );

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->filename($report, 'pdf').'"',
            'Content-Length' => (string) strlen($content),
        ]);
    }

    /** Excel file of everything the filters match (not just the page on screen). */
    public function excel(Request $request): BinaryFileResponse
    {
        $this->allowLongExport();
        $report = new CustomerSummaryReport($request);
        $totals = $report->totals();

        $path = SimpleXlsx::build(
            'Customer summary',
            [
                $report->titleWithMonth(),
                'Filters: '.$report->activeFilterText().'   |   Generated '.now()->format('d M Y H:i'),
            ],
            CustomerSummaryReport::HEADINGS,
            $report->allRows(),
            [0 => 'int', 9 => 'money', 10 => 'money', 11 => 'money'],
            [0 => 9, 1 => 22, 2 => 11, 3 => 13, 4 => 28, 5 => 15, 6 => 20, 7 => 15, 8 => 11, 9 => 14, 10 => 16, 11 => 16],
            [
                null, null, null, null, 'Total ('.number_format($totals['customers']).' customers)', null, null, null, null,
                round($totals['usage_units'], 2),
                round($totals['month_bill'], 2),
                round($totals['outstanding'], 2),
            ],
            CustomerSummaryReport::DEVELOPER_CREDIT
        );

        return response()->download($path, $this->filename($report, 'xlsx'), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function filename(CustomerSummaryReport $report, string $ext): string
    {
        $name = $report->isMissingPhoneView() ? 'customers-without-phone' : 'customer-summary';

        return $name.'-'.$report->filters['month'].'.'.$ext;
    }

    /** A full customer list is a big file; give PDF/Excel creation room to finish. */
    private function allowLongExport(): void
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');
    }
}
