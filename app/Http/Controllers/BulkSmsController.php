<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Division;
use App\Models\SystemConfiguration;
use App\Services\NotifyLkSmsService;
use App\Services\SmsNotificationService;
use App\Services\SmsTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BulkSmsController extends Controller
{
    public function create(Request $request): View
    {
        $customers = collect();
        if ($request->filled('search') || $request->filled('filter_division') || $request->filled('filter_status')) {
            $customers = $this->search($request)->limit(40)->get();
        }

        return view('sms.bulk', [
            'divisions' => Division::orderBy('name')->get(),
            'customers' => $customers,
            'eligibleCount' => $this->eligibleQuery()->count(),
            'starter' => \App\Models\SmsTemplate::bodyFor('custom', 'Dear {customer_name}, account {account_number}: '),
            'costPerSegment' => (float) SystemConfiguration::get('sms_cost_per_segment', 0),
        ]);
    }

    public function store(Request $request, SmsNotificationService $sms, SmsTemplateService $templates, NotifyLkSmsService $gateway): RedirectResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:480',
            'division_ids' => 'nullable|array',
            'division_ids.*' => 'integer|exists:divisions,id',
            'customer_ids' => 'nullable|array',
            'customer_ids.*' => 'integer|exists:customers,id',
            'send_all' => 'nullable|boolean',
        ]);

        $recipients = $this->recipients($validated);
        $first = $recipients->first();
        $preview = $first
            ? $templates->replaceVariables($validated['message'], $this->variables($first, $templates))
            : $templates->previewTemplate('custom', ['customer_name' => 'Sample Customer', 'account_number' => 'CP/NE/DN/FD/001']);
        $segments = NotifyLkSmsService::segmentCount($preview);
        $cost = $segments * (float) SystemConfiguration::get('sms_cost_per_segment', 0) * $recipients->count();

        $previewMeta = [
            'recipients' => $recipients->count(),
            'segments' => $segments,
            'cost' => $cost,
        ];

        if ($request->boolean('preview')) {
            return back()->withInput()->with('preview', $preview)->with('preview_meta', $previewMeta);
        }

        if (!$request->boolean('confirm_send')) {
            return back()->withInput()->with('preview', $preview)->with('preview_meta', $previewMeta)
                ->with('error', 'Tick the confirmation box before sending.');
        }

        if ($recipients->isEmpty()) {
            return back()->withInput()->with('error', 'Choose at least one customer with a valid mobile number.');
        }

        $queued = 0;
        $skipped = 0;
        $seen = [];

        foreach ($recipients as $customer) {
            $phone = preg_replace('/\D+/', '', (string) $customer->phone);
            if ($phone === '' || isset($seen[$phone]) || !$gateway->hasValidPhone($customer->phone)) {
                $skipped++;
                continue;
            }
            $seen[$phone] = true;
            $message = $templates->replaceVariables($validated['message'], $this->variables($customer, $templates));
            if ($sms->sendCustomNotification($customer, $message)) {
                $queued++;
            } else {
                $skipped++;
            }
        }

        return redirect()->route('sms.bulk')->with('success', "Queued {$queued} custom messages. Skipped {$skipped}.");
    }

    private function recipients(array $validated)
    {
        $ids = collect();

        if (!empty($validated['send_all'])) {
            $ids = $ids->merge($this->eligibleQuery()->pluck('id'));
        }

        if (!empty($validated['division_ids'])) {
            $ids = $ids->merge($this->eligibleQuery()->whereIn('division_id', $validated['division_ids'])->pluck('id'));
        }

        if (!empty($validated['customer_ids'])) {
            $ids = $ids->merge($validated['customer_ids']);
        }

        return Customer::with('bills')->whereIn('id', $ids->unique()->all())->get();
    }

    private function variables(Customer $customer, SmsTemplateService $templates): array
    {
        $bill = $customer->bills
            ->where('balance_amount', '>', 0)
            ->sortByDesc('bill_date')
            ->first();

        $values = [
            'customer_name' => $customer->full_name,
            'account_number' => $customer->account_number,
        ];

        return $bill ? array_merge($templates->billVariables($bill), $values) : $values;
    }

    private function eligibleQuery()
    {
        return Customer::query()->where('status', 'active')->whereNotNull('phone')->where('phone', '!=', '');
    }

    private function search(Request $request)
    {
        $query = Customer::query()->with('division');

        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
        }

        if ($request->filled('filter_division')) {
            $query->where('division_id', $request->filter_division);
        }

        if ($request->filled('search')) {
            $term = '%'.$request->search.'%';
            $query->where(function ($inner) use ($term) {
                $inner->where('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('account_number', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        return $query->orderBy('account_number');
    }
}
