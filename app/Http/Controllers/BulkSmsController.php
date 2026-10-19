<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Division;
use App\Services\SmsNotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BulkSmsController extends Controller
{
    public function create(): View
    {
        return view('sms.bulk', [
            'divisions' => Division::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, SmsNotificationService $sms)
    {
        $validated = $request->validate([
            'audience' => 'required|in:all,defaulters,division,disconnected,selected',
            'division_id' => 'nullable|exists:divisions,id',
            'customer_ids' => 'nullable|string',
            'message' => 'required|string|max:480',
        ]);

        $customers = $this->audience($validated);
        $sent = 0;

        foreach ($customers as $customer) {
            try {
                $sms->sendCustomNotification($customer, $validated['message']);
                $sent++;
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return redirect()->route('sms.bulk')->with('success', $sent.' message'.($sent === 1 ? '' : 's').' queued.');
    }

    private function audience(array $validated)
    {
        $query = Customer::query()->whereNotNull('phone')->where('phone', '!=', '');

        return match ($validated['audience']) {
            'defaulters' => $query->whereHas('bills', fn ($bills) => $bills->where('balance_amount', '>', 0)->whereNotIn('status', ['cancelled', 'draft', 'paid']))->get(),
            'division' => $query->where('division_id', $validated['division_id'] ?? 0)->get(),
            'disconnected' => $query->where('status', 'disconnected')->get(),
            'selected' => $query->whereIn('id', array_filter(array_map('intval', explode(',', $validated['customer_ids'] ?? ''))))->get(),
            default => $query->where('status', 'active')->get(),
        };
    }
}
