<?php

namespace App\Observers;

use App\Events\ReportChanged;
use App\Models\Report;
use App\Services\NotificationRouter;

class ReportObserver
{
    public function created(Report $item): void
    {
        try {
            event(new ReportChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('reports.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for Report: " . $e->getMessage());
        }
    }

    public function updated(Report $item): void
    {
        try {
            event(new ReportChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('reports.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for Report: " . $e->getMessage());
        }
    }

    public function deleted(Report $item): void
    {
        try {
            event(new ReportChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('reports.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for Report: " . $e->getMessage());
        }
    }
}
