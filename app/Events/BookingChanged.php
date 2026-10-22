<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Booking $item,
        public string $action,
    ) {}
}
