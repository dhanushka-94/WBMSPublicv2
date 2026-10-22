<?php

namespace App\Http\Controllers;

use App\Models\SmsTemplate;
use App\Services\SmsTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsTemplateController extends Controller
{
    public function index(SmsTemplateService $templates): View
    {
        $rows = SmsTemplate::orderBy('name')->get()->map(function (SmsTemplate $template) use ($templates) {
            $template->preview = $templates->previewTemplate($template->key);

            return $template;
        });

        return view('sms.templates', [
            'templates' => $rows,
            'placeholders' => [
                '{customer_name}', '{account_number}', '{bill_number}', '{billing_period}',
                '{bill_amount}', '{paid_amount}', '{outstanding_amount}', '{due_date}',
                '{payment_date}', '{receipt_number}', '{disconnection_date}',
                '{bill_link}', '{receipt_link}', '{payment_link}',
            ],
        ]);
    }

    public function update(Request $request, SmsTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'body' => 'required|string|max:480',
            'is_active' => 'nullable|boolean',
        ]);

        $template->update([
            'body' => $validated['body'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('sms.templates')->with('success', $template->name.' saved.');
    }
}
