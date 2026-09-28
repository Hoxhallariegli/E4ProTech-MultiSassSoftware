<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'barber_shop_id' => $this->barber_shop_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'photo' => $this->photo ? asset(ltrim((string) $this->photo, '/')) : null,
            'total_bookings' => $this->total_bookings,
            'no_show_count' => $this->no_show_count,
            'blocked_at' => $this->blocked_at,
            'barberShop' => $this->whenLoaded('barberShop'),
        ];
    }
}