<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'barber_shop_id' => $this->barber_shop_id,
            'customer_id' => $this->customer_id,
            'channel' => $this->channel,
            'message' => $this->message,
            'status' => $this->status,
            'sent_at' => $this->sent_at,
            'barberShop' => $this->whenLoaded('barberShop'),
            'customer' => $this->whenLoaded('customer'),
        ];
    }
}