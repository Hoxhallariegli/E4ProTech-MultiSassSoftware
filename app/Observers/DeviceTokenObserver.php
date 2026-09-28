<?php

namespace App\Observers;

use App\Events\DeviceTokenChanged;
use App\Models\DeviceToken;
use App\Services\NotificationRouter;

class DeviceTokenObserver
{
    public function created(DeviceToken $item): void
    {
        try {
            event(new DeviceTokenChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('device-tokens.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for DeviceToken: " . $e->getMessage());
        }
    }

    public function updated(DeviceToken $item): void
    {
        try {
            event(new DeviceTokenChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('device-tokens.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for DeviceToken: " . $e->getMessage());
        }
    }

    public function deleted(DeviceToken $item): void
    {
        try {
            event(new DeviceTokenChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('device-tokens.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for DeviceToken: " . $e->getMessage());
        }
    }
}
