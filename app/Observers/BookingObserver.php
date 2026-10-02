<?php

namespace App\Observers;

use App\Events\BookingChanged;
use App\Models\Booking;
use App\Models\Customer;
use App\Services\NotificationRouter;

class BookingObserver
{
    public function created(Booking $item): void
    {
        try {
            \Illuminate\Support\Facades\Log::info("📌 [STEP 1] BookingObserver::created triggered for Booking #{$item->id} (Shop #{$item->barber_shop_id})");
            Customer::recalculateStats($item->customer_id);
            event(new BookingChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('bookings.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("❌ Observer (created) failure for Booking #{$item->id}: " . $e->getMessage());
        }
    }

    public function updated(Booking $item): void
    {
        try {
            Customer::recalculateStats($item->customer_id);
            if ($item->isDirty('customer_id') && $item->getOriginal('customer_id')) {
                Customer::recalculateStats((int) $item->getOriginal('customer_id'));
            }
            event(new BookingChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('bookings.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for Booking: " . $e->getMessage());
        }
    }

    public function deleted(Booking $item): void
    {
        try {
            Customer::recalculateStats($item->customer_id);
            event(new BookingChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('bookings.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for Booking: " . $e->getMessage());
        }
    }
}
