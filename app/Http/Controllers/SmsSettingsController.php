<?php

namespace App\Http\Controllers;

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
            'cost' => (string) SystemConfiguration::get('sms_cost_per_segment', '0'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->adminOnly();

        $validated = $request->validate([
            'user_id' => 'nullable|string|max:100',
            'api_key' => 'nullable|string|max:255',
            'sender_id' => 'nullable|string|max:20',
            'cost_per_segment' => 'nullable|numeric|min:0|max:1000',
        ]);

        SystemConfiguration::set('sms_user_id', $validated['user_id'] ?? '', 'string', 'Notify.lk user id');
        SystemConfiguration::set('sms_sender_id', $validated['sender_id'] ?? '', 'string', 'Notify.lk sender id');
        SystemConfiguration::set('sms_enabled', $request->boolean('enabled') ? '1' : '0', 'string', 'SMS sending enabled');
        SystemConfiguration::set('sms_cost_per_segment', (string) ($validated['cost_per_segment'] ?? '0'), 'string', 'Estimated cost per SMS segment');

        if (($validated['api_key'] ?? '') !== '') {
            SystemConfiguration::set('sms_api_key', $validated['api_key'], 'string', 'Notify.lk API key');
        }

        $errors = app(NotifyLkSmsService::class)->validateConfiguration();

        return redirect()->route('sms.settings')->with(
            'success',
            $errors === [] ? 'SMS settings saved.' : 'SMS settings saved. '.implode(' ', $errors)
        );
    }

    private function adminOnly(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->role === 'super_admin', 403);
    }
}
