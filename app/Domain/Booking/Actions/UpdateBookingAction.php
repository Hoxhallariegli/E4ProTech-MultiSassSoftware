<?php

namespace App\Domain\Booking\Actions;

use App\Models\Booking;
use App\Domain\Booking\DTOs\BookingDTO;
use App\Models\AuditTrail;

class UpdateBookingAction
{
    public function execute(Booking $model, BookingDTO $dto, bool $allowLunchOverride = false): Booking
    {
        Booking::checkOverlap($dto->barber_id, $dto->appointment_at, $dto->service_id, $model->id, $allowLunchOverride);
        $data = $dto->toArray();
        $model->fill($data);
        $dirty = $model->getDirty();
        $original = $model->getOriginal();
        $model->save();

        if (!empty($dirty)) {
            AuditTrail::create([
                'user_id' => auth()->id(),
                'title' => "Update Booking",
                'reference_id' => (string)$model->id,
                'section' => 'Bookings',
                'type' => 'update',
                'old_values' => array_intersect_key($original, $dirty),
                'new_values' => $dirty,
            ]);
        }

        if (isset($data['total_price'])) {
            \App\Models\Payment::where('booking_id', $model->id)->update([
                'amount' => $model->total_price,
            ]);
        }

        return $model->fresh();
    }
}
