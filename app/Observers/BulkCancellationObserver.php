<?php

namespace App\Observers;

use App\Events\BulkCancellationChanged;
use App\Models\BulkCancellation;
use App\Services\NotificationRouter;

class BulkCancellationObserver
{
    public function created(BulkCancellation $item): void
    {
        try {
            event(new BulkCancellationChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('bulk-cancellations.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for BulkCancellation: " . $e->getMessage());
        }
    }

    public function updated(BulkCancellation $item): void
    {
        try {
            event(new BulkCancellationChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('bulk-cancellations.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for BulkCancellation: " . $e->getMessage());
        }
    }

    public function deleted(BulkCancellation $item): void
    {
        try {
            event(new BulkCancellationChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('bulk-cancellations.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for BulkCancellation: " . $e->getMessage());
        }
    }
}
