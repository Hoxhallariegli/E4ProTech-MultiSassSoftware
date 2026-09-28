<?php

namespace App\Observers;

use App\Events\PlanChanged;
use App\Models\Plan;
use App\Services\NotificationRouter;

class PlanObserver
{
    public function created(Plan $item): void
    {
        try {
            event(new PlanChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('plans.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for Plan: " . $e->getMessage());
        }
    }

    public function updated(Plan $item): void
    {
        try {
            event(new PlanChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('plans.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for Plan: " . $e->getMessage());
        }
    }

    public function deleted(Plan $item): void
    {
        try {
            event(new PlanChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('plans.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for Plan: " . $e->getMessage());
        }
    }
}
