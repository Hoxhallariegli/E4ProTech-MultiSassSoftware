<?php

namespace App\Domain\Booking\Actions;

use App\Models\Booking;
use App\Domain\Booking\DTOs\BookingDTO;
use App\Models\AuditTrail;

class CreateBookingAction
{
    public function execute(BookingDTO $dto, bool $allowLunchOverride = false): Booking
    {
        Booking::checkOverlap($dto->barber_id, $dto->appointment_at, $dto->service_id, null, $allowLunchOverride);
        $item = Booking::create($dto->toArray());
        AuditTrail::log($item, 'create', 'Bookings');
        return $item;
    }
}
