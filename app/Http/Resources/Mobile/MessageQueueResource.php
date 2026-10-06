<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageQueueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $type = $this->resolved_template_type;
        $typeLabel = $type === 'reminder' ? 'Rikujtesë SMS' : ($type === 'welcome' ? 'Mirëseardhje SMS' : 'Konfirmim SMS');

        return [
            'id' => $this->id,
            'barber_shop_id' => $this->barber_shop_id,
            'booking_id' => $this->booking_id,
            'channel' => $this->channel,
            'template_type' => $type,
            'template_type_label' => $typeLabel,
            'phone_number' => $this->phone_number,
            'message_content' => $this->message_content,
            'scheduled_at' => $this->scheduled_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'status' => $this->status,
            'retry_count' => $this->retry_count,
            'appointment_at' => $this->booking?->appointment_at?->toIso8601String(),
            'customer_name' => $this->booking?->customer?->name ?? null,
            'barberShop' => $this->whenLoaded('barberShop'),
            'booking' => $this->whenLoaded('booking'),
        ];
    }
}
