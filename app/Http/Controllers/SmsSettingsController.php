<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\SmsRateChange;
use App\Models\SystemConfiguration;
use App\Services\NotifyLkSmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsSettingsController extends Controller
{
    public function edit(): View
    {
        $this->adminOnly();

        return view('sms.settings', [
            'userId' => (string) SystemConfiguration::get('sms_user_id', config('sms.drivers.notify_lk.user_id', '')),
            'senderId' => (string) SystemConfiguration::get('sms_sender_id', config('sms.drivers.notify_lk.sender_id', 'NotifyDEMO')),
            'hasApiKey' => (string) SystemConfiguration::get('sms_api_key', config('sms.drivers.notify_lk.api_key', '')) !== '',
            'enabled' => SystemConfiguration::get('sms_enabled', '1') !== '0',
            'cost' => SmsRateChange::currentRate(),
            'rateHistory' => SmsRateChange::query()->orderByDesc('id')->limit(50)->get(),
            'rateHistoryCount' => SmsRateChange::query()->count(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->adminOnly();

        $validated = $request->validate([
            'user_id' => 'nullable|string|max:100',
            'api_key' => 'nullable|string|max:255',
            'sender_id' => 'nullable|string|max:20',
            'cost_per_segment' => 'required|numeric|min:0|max:1000',
            'rate_note' => 'nullable|string|max:255',
        ], [], ['cost_per_segment' => 'cost per SMS segment']);

        $rateChange = SmsRateChange::change((float) $validated['cost_per_segment'], $request->user(), $validated['rate_note'] ?? null);
        if ($rateChange) {
            $this->auditRateChange($rateChange);
        }

        SystemConfiguration::set('sms_user_id', $validated['user_id'] ?? '', 'string', 'Notify.lk user id');
        SystemConfiguration::set('sms_sender_id', $validated['sender_id'] ?? '', 'string', 'Notify.lk sender id');
        SystemConfiguration::set('sms_enabled', $request->boolean('enabled') ? '1' : '0', 'string', 'SMS sending enabled');
        if (($validated['api_key'] ?? '') !== '') {
            SystemConfiguration::set('sms_api_key', $validated['api_key'], 'string', 'Notify.lk API key');
        }

        $errors = app(NotifyLkSmsService::class)->validateConfiguration();

        $message = 'SMS settings saved.';
        if ($rateChange) {
            $message .= ' Cost per segment changed from Rs. '.number_format((float) $rateChange->previous_rate, 2).' to Rs. '.number_format((float) $rateChange->rate, 2).'.';
        }

        return redirect()->route('sms.settings')->with(
            'success',
            $errors === [] ? $message : $message.' '.implode(' ', $errors)
        );
    }

    private function auditRateChange(SmsRateChange $change): void
    {
        try {
            ActivityLog::logActivity([
                'action' => 'sms_rate_changed',
                'module' => 'sms',
                'description' => (auth()->user()?->name ?? 'A user').' changed the SMS cost per segment from Rs. '
                    .number_format((float) $change->previous_rate, 2).' to Rs. '.number_format((float) $change->rate, 2),
                'properties' => [
                    'previous_rate' => $change->previous_rate,
                    'new_rate' => $change->rate,
                    'note' => $change->note,
                ],
                'status' => 'success',
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function adminOnly(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->role === 'super_admin', 403);
    }
}
