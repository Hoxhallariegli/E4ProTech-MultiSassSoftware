<?php

namespace App\Observers;

use App\Events\BarberChanged;
use App\Models\Barber;
use App\Services\NotificationRouter;

class BarberObserver
{
    public function created(Barber $item): void
    {
        try {
            event(new BarberChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('barbers.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for Barber: " . $e->getMessage());
        }
    }

    public function updated(Barber $item): void
    {
        try {
            event(new BarberChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('barbers.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for Barber: " . $e->getMessage());
        }
    }

    public function deleted(Barber $item): void
    {
        try {
            event(new BarberChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('barbers.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for Barber: " . $e->getMessage());
        }
    }
}
