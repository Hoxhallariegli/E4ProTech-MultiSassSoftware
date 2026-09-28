<?php

namespace App\Observers;

use App\Events\EventSettingChanged;
use App\Models\EventSetting;
use App\Services\NotificationRouter;

class EventSettingObserver
{
    public function created(EventSetting $item): void
    {
        try {
            event(new EventSettingChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('event-settings.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for EventSetting: " . $e->getMessage());
        }
    }

    public function updated(EventSetting $item): void
    {
        try {
            event(new EventSettingChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('event-settings.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for EventSetting: " . $e->getMessage());
        }
    }

    public function deleted(EventSetting $item): void
    {
        try {
            event(new EventSettingChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('event-settings.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for EventSetting: " . $e->getMessage());
        }
    }
}
