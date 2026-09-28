<?php

namespace App\Domain\Booking\Actions;

use App\Models\Booking;
use App\Models\AuditTrail;

class DeleteBookingAction
{
    public function execute(Booking $model): bool 
    {
        AuditTrail::log($model, 'delete', 'Bookings');
        return $model->delete(); 
    }
}