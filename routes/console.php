<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automatic bill generation on the 20th of each month at 6:00 AM
Schedule::command('bills:generate-monthly')
    ->monthlyOn(20, '06:00')
    ->timezone('Asia/Colombo')
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        Log::info('Monthly bill generation completed successfully');
    })
    ->onFailure(function () {
        Log::error('Monthly bill generation failed');
    });

// Schedule late fee calculation on the 1st of each month at 7:00 AM
Schedule::command('bills:calculate-late-fees')
    ->monthlyOn(1, '07:00')
    ->timezone('Asia/Colombo')
    ->withoutOverlapping()
    ->runInBackground();

// Schedule daily backup of critical data at 2:00 AM
Schedule::command('backup:run')
    ->dailyAt('02:00')
    ->timezone('Asia/Colombo')
    ->withoutOverlapping()
    ->runInBackground();
