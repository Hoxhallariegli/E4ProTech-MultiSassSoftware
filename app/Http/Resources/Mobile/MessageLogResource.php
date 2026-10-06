<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $type = $this->resolved_template_type;
        $typeLabel = $type === 'reminder' ? 'Rikujtesë SMS' : ($type === 'welcome' ? 'Mirëseardhje SMS' : 'Konfirmim SMS');

        $booking = $this->customer_id ? \App\Models\Booking::where('customer_id', $this->customer_id)->latest('id')->first() : null;

        return [
            'id' => $this->id,
            'barber_shop_id' => $this->barber_shop_id,
            'customer_id' => $this->customer_id,
            'channel' => $this->channel,
            'template_type' => $type,
            'template_type_label' => $typeLabel,
            'message' => $this->message,
            'status' => $this->status,
            'sent_at' => $this->sent_at,
            'created_at' => $this->created_at,
            'scheduled_at' => $this->created_at,
            'appointment_at' => $booking?->appointment_at?->toIso8601String(),
            'customer_name' => $this->customer?->name,
            'customer_phone' => $this->customer?->phone,
            'barberShop' => $this->whenLoaded('barberShop'),
            'customer' => $this->whenLoaded('customer'),
        ];
    }
}
