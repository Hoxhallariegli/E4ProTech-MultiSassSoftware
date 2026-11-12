<?php

namespace App\Domain\Barber\Actions;

use App\Models\Barber;
use App\Models\AuditTrail;

class DeleteBarberAction
{
    public function execute(Barber $model): bool
    {
        if ($model->photo) { app(\App\Services\ImageUploadService::class)->delete($model->photo); }
        AuditTrail::log($model, 'delete', 'Barbers');
        return $model->delete();
    }

    public function forceExecute(Barber $model): bool
    {
        if ($model->photo) {
            app(\App\Services\ImageUploadService::class)->delete($model->photo);
        }

        // Get all booking IDs associated with this barber
        $bookingIds = \App\Models\Booking::withoutGlobalScope('barber_shop_access')
            ->where('barber_id', $model->id)
            ->pluck('id');

        if ($bookingIds->isNotEmpty()) {
            // Delete payments connected to these bookings
            \App\Models\Payment::withoutGlobalScope('barber_shop_access')
                ->whereIn('booking_id', $bookingIds)
                ->delete();

            // Delete message logs connected to these bookings
            \App\Models\MessageLog::withoutGlobalScope('barber_shop_access')
                ->whereIn('booking_id', $bookingIds)
                ->delete();

            // Delete message queues connected to these bookings
            \App\Models\MessageQueue::withoutGlobalScope('barber_shop_access')
                ->whereIn('booking_id', $bookingIds)
                ->delete();

            // Delete bookings
            \App\Models\Booking::withoutGlobalScope('barber_shop_access')
                ->whereIn('id', $bookingIds)
                ->delete();
        }

        // Delete working hours & bulk cancellations for this barber
        \App\Models\WorkingHour::withoutGlobalScope('barber_shop_access')->where('barber_id', $model->id)->delete();
        \App\Models\BulkCancellation::withoutGlobalScope('barber_shop_access')->where('barber_id', $model->id)->delete();

        AuditTrail::logCustom($model, 'delete', 'Barbers', "Fshirje e detyruar (Force Delete Admin) e berberit #{$model->id}");

        return $model->forceDelete();
    }
}
