<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ReportHubController extends Controller
{
    public function index(): View
    {
        $sections = [
            [
                'title' => 'Connections',
                'items' => [
                    ['Monthly Active', 'Active connections by month', 'reports.monthly-active', 'fa-users-cog', 'text-green-600'],
                    ['Customer Register', 'Full customer listing', 'reports.office', 'fa-address-book', 'text-blue-600', 'customer-register'],
                    ['Contact List', 'Customer phones and contacts', 'reports.office', 'fa-address-card', 'text-cyan-600', 'contacts'],
                    ['New Connections', 'Recently connected customers', 'reports.office', 'fa-plug', 'text-green-600', 'new-connections'],
                    ['Disconnected Customers', 'Disconnected accounts', 'reports.office', 'fa-unlink', 'text-red-600', 'disconnected'],
                ],
            ],
            [
                'title' => 'Usage',
                'items' => [
                    ['Consumption', 'Water usage trends', 'reports.consumption', 'fa-chart-line', 'text-purple-600'],
                    ['Reader Routes', 'Route completion status', 'reports.reader-routes', 'fa-clipboard-check', 'text-blue-600'],
                    ['Reader Statistics', 'Reader activity summary', 'reports.reader-statistics', 'fa-chart-pie', 'text-cyan-600'],
                    ['Usage Alerts', 'Unusual consumption', 'reports.office', 'fa-bell', 'text-amber-600', 'usage-alerts'],
                    ['Meter Faults', 'Fault and maintenance flags', 'reports.office', 'fa-tools', 'text-orange-600', 'meter-faults'],
                ],
            ],
            [
                'title' => 'Collections',
                'items' => [
                    ['Revenue', 'Revenue overview', 'reports.revenue', 'fa-chart-bar', 'text-indigo-600'],
                    ['Overdue', 'Overdue bills', 'reports.overdue', 'fa-exclamation-triangle', 'text-red-600'],
                    ['Billing Summary', 'Billing totals', 'reports.office', 'fa-file-invoice-dollar', 'text-emerald-600', 'billing-summary'],
                    ['Daily Collection', 'Payments by day', 'reports.office', 'fa-coins', 'text-yellow-600', 'daily-collection'],
                    ['Monthly Collection', 'Payments by month', 'reports.office', 'fa-calendar-alt', 'text-indigo-600', 'monthly-collection'],
                    ['Collection Efficiency', 'Collection performance', 'reports.office', 'fa-percentage', 'text-purple-600', 'collection-efficiency'],
                    ['Age Analysis', 'Outstanding age buckets', 'reports.office', 'fa-hourglass-half', 'text-rose-600', 'age-analysis'],
                ],
            ],
        ];

        return view('reports.index', compact('sections'));
    }
}
