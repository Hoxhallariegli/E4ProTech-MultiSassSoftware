<?php

namespace App\Observers;

use App\Events\NotificationChannelChanged;
use App\Models\NotificationChannel;
use App\Services\NotificationRouter;

class NotificationChannelObserver
{
    public function created(NotificationChannel $item): void
    {
        try {
            event(new NotificationChannelChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('notification-channels.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for NotificationChannel: " . $e->getMessage());
        }
    }

    public function updated(NotificationChannel $item): void
    {
        try {
            event(new NotificationChannelChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('notification-channels.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for NotificationChannel: " . $e->getMessage());
        }
    }

    public function deleted(NotificationChannel $item): void
    {
        try {
            event(new NotificationChannelChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('notification-channels.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for NotificationChannel: " . $e->getMessage());
        }
    }
}
