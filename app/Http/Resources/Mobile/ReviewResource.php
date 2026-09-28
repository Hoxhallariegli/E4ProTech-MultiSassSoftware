<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'barber_shop_id' => $this->barber_shop_id,
            'barber_id' => $this->barber_id,
            'customer_id' => $this->customer_id,
            'booking_id' => $this->booking_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'barberShop' => $this->whenLoaded('barberShop'),
            'barber' => $this->whenLoaded('barber'),
            'customer' => $this->whenLoaded('customer'),
            'booking' => $this->whenLoaded('booking'),
        ];
    }
}