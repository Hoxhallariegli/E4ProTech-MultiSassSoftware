<?php

namespace App\Observers;

use App\Events\WorkingHourChanged;
use App\Models\WorkingHour;
use App\Services\NotificationRouter;

class WorkingHourObserver
{
    public function created(WorkingHour $item): void
    {
        try {
            event(new WorkingHourChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('working-hours.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for WorkingHour: " . $e->getMessage());
        }
    }

    public function updated(WorkingHour $item): void
    {
        try {
            event(new WorkingHourChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('working-hours.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for WorkingHour: " . $e->getMessage());
        }
    }

    public function deleted(WorkingHour $item): void
    {
        try {
            event(new WorkingHourChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('working-hours.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for WorkingHour: " . $e->getMessage());
        }
    }
}
