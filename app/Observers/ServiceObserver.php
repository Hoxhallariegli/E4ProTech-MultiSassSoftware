<?php

namespace App\Observers;

use App\Events\ServiceChanged;
use App\Models\Service;
use App\Services\NotificationRouter;

class ServiceObserver
{
    public function created(Service $item): void
    {
        try {
            event(new ServiceChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('services.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for Service: " . $e->getMessage());
        }
    }

    public function updated(Service $item): void
    {
        try {
            event(new ServiceChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('services.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for Service: " . $e->getMessage());
        }
    }

    public function deleted(Service $item): void
    {
        try {
            event(new ServiceChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('services.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for Service: " . $e->getMessage());
        }
    }
}
