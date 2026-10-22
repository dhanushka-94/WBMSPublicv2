<?php

namespace App\Jobs;

use App\Models\SmsNotification;
use App\Services\SmsNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $notificationId)
    {
    }

    public function handle(SmsNotificationService $sms): void
    {
        $notification = SmsNotification::find($this->notificationId);

        if ($notification && $notification->status === 'pending') {
            $sms->sendExisting($notification);
        }
    }
}
