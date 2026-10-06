<?php

namespace App\Observers;

use App\Events\BookingChanged;
use App\Events\FirebaseNotificationRequested;
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

            event(new FirebaseNotificationRequested(
                event: 'bookings.created',
                modelClass: Booking::class,
                modelId: $item->getKey(),
                action: 'created',
            ));
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

            event(new FirebaseNotificationRequested(
                event: 'bookings.updated',
                modelClass: Booking::class,
                modelId: $item->getKey(),
                action: 'updated',
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for Booking: " . $e->getMessage());
        }
    }

    public function deleted(Booking $item): void
    {
        try {
            Customer::recalculateStats($item->customer_id);
            event(new BookingChanged($item, 'deleted'));

            event(new FirebaseNotificationRequested(
                event: 'bookings.deleted',
                modelClass: Booking::class,
                modelId: $item->getKey(),
                action: 'deleted',
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for Booking: " . $e->getMessage());
        }
    }
}
