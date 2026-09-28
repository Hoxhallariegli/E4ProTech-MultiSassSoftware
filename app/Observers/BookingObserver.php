<?php

namespace App\Observers;

use App\Events\BookingChanged;
use App\Models\Booking;
use App\Services\NotificationRouter;

class BookingObserver
{
    public function created(Booking $item): void
    {
        try {
            event(new BookingChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('bookings.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for Booking: " . $e->getMessage());
        }
    }

    public function updated(Booking $item): void
    {
        try {
            event(new BookingChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('bookings.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for Booking: " . $e->getMessage());
        }
    }

    public function deleted(Booking $item): void
    {
        try {
            event(new BookingChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('bookings.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for Booking: " . $e->getMessage());
        }
    }
}
