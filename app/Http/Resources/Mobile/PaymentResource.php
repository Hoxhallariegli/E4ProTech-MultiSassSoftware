<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $booking = $this->booking;
        $customerName = $booking?->customer?->name ?? 'Klient';

        $serviceName = $booking?->service?->name ?? 'Shërbim';
        if ($booking && $booking->notes && str_starts_with($booking->notes, 'Shërbimet: ')) {
            $serviceNamesString = trim(substr($booking->notes, strlen('Shërbimet: ')));
            $names = array_map('trim', explode('+', $serviceNamesString));
            if (!empty($names)) {
                $serviceName = implode(' + ', $names);
            }
        }

        return [
            'id' => $this->id,
            'barber_shop_id' => $this->barber_shop_id,
            'booking_id' => $this->booking_id,
            'amount' => $this->amount,
            'method' => $this->method,
            'status' => $this->status,
            'customer_name' => $customerName,
            'service_name' => $serviceName,
            'barber_name' => $booking?->barber?->name,
            'created_at' => $this->created_at,
            'barberShop' => $this->whenLoaded('barberShop'),
            'booking' => $this->relationLoaded('booking') && $this->booking ? new BookingResource($this->booking) : null,
        ];
    }
}
