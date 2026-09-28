<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $serviceName = $this->service?->name ?? 'Shërbim';
        $notes = $this->notes ?? '';
        if (str_starts_with($notes, 'Shërbimet: ')) {
            $serviceNamesString = trim(substr($notes, strlen('Shërbimet: ')));
            $names = array_map('trim', explode('+', $serviceNamesString));
            if (!empty($names)) {
                $serviceName = implode(' + ', $names);
            }
        }

        $paymentRecord = \App\Models\Payment::where('booking_id', $this->id)->latest()->first();
        $paymentStatus = $paymentRecord?->status ?? $this->payment_status ?? 'unpaid';
        $isPaid = ($paymentStatus === 'paid');

        $displayPrice = ($paymentRecord && (float) $paymentRecord->amount > 0)
            ? $paymentRecord->amount
            : (($this->total_price && (float) $this->total_price > 0)
                ? $this->total_price
                : ($this->service?->price ?? 0));

        return [
            'id' => $this->id,
            'name' => 'Takimi #' . $this->id . ' - ' . ($this->customer?->name ?? 'Klient') . ' (' . $serviceName . ' - ' . $displayPrice . ' Lekë)',
            'barber_shop_id' => $this->barber_shop_id,
            'barber_id' => $this->barber_id,
            'service_id' => $this->service_id,
            'customer_id' => $this->customer_id,
            'appointment_at' => $this->appointment_at,
            'status' => $this->status,
            'payment_status' => $paymentStatus,
            'total_price' => $displayPrice,
            'notes' => $this->notes,
            'source' => $this->source,
            'service_name' => $serviceName,
            'barberShop' => $this->whenLoaded('barberShop'),
            'barber' => $this->whenLoaded('barber'),
            'service' => $this->whenLoaded('service'),
            'customer' => $this->whenLoaded('customer'),
        ];
    }
}
