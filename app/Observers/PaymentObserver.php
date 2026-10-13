<?php

namespace App\Observers;

use App\Events\PaymentChanged;
use App\Models\Payment;
use App\Services\NotificationRouter;

class PaymentObserver
{
    public function created(Payment $item): void
    {
        try {
            event(new PaymentChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('payments.created', $item, 'created');
            $this->syncBookingPayment($item);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for Payment: " . $e->getMessage());
        }
    }

    public function updated(Payment $item): void
    {
        try {
            event(new PaymentChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('payments.updated', $item, 'updated');
            $this->syncBookingPayment($item);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for Payment: " . $e->getMessage());
        }
    }

    public function deleted(Payment $item): void
    {
        try {
            event(new PaymentChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('payments.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for Payment: " . $e->getMessage());
        }
    }

    protected function syncBookingPayment(Payment $item): void
    {
        if ($item->booking_id && $item->status === 'paid') {
            $booking = \App\Models\Booking::find($item->booking_id);
            if ($booking) {
                $updateData = ['payment_status' => 'paid'];
                if ($booking->status === 'pending') {
                    $updateData['status'] = 'confirmed';
                }
                $booking->update($updateData);
            }
        }
    }
}
