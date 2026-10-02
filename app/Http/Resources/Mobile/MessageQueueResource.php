<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageQueueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'barber_shop_id' => $this->barber_shop_id,
            'booking_id' => $this->booking_id,
            'channel' => $this->channel,
            'phone_number' => $this->phone_number,
            'message_content' => $this->message_content,
            'scheduled_at' => $this->scheduled_at,
            'status' => $this->status,
            'retry_count' => $this->retry_count,
            'customer_name' => $this->booking?->customer?->name ?? null,
            'barberShop' => $this->whenLoaded('barberShop'),
            'booking' => $this->whenLoaded('booking'),
        ];
    }
}
