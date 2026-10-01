<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// 1. Process Message Queue (Try to send pending SMS to gateways)
Schedule::command('sms:send-queued')->everyMinute()->withoutOverlapping();

// 2. Generate Reminder SMS Queues (X hours before appointment)
Schedule::command('sms:send-reminders')->everyFiveMinutes()->withoutOverlapping();
