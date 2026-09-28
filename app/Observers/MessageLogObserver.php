<?php

namespace App\Observers;

use App\Events\MessageLogChanged;
use App\Models\MessageLog;
use App\Services\NotificationRouter;

class MessageLogObserver
{
    public function created(MessageLog $item): void
    {
        try {
            event(new MessageLogChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('message-logs.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for MessageLog: " . $e->getMessage());
        }
    }

    public function updated(MessageLog $item): void
    {
        try {
            event(new MessageLogChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('message-logs.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for MessageLog: " . $e->getMessage());
        }
    }

    public function deleted(MessageLog $item): void
    {
        try {
            event(new MessageLogChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('message-logs.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for MessageLog: " . $e->getMessage());
        }
    }
}
