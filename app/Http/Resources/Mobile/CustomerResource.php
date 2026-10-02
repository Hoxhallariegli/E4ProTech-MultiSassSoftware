<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $realTotal = \App\Models\Booking::where('customer_id', $this->id)->where('status', '!=', 'cancelled')->count();
        $realNoShow = \App\Models\Booking::where('customer_id', $this->id)->where('status', 'no-show')->count();

        return [
            'id' => $this->id,
            'barber_shop_id' => $this->barber_shop_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'photo' => $this->photo ? asset(ltrim((string) $this->photo, '/')) : null,
            'total_bookings' => max((int) ($this->total_bookings ?? 0), $realTotal),
            'no_show_count' => max((int) ($this->no_show_count ?? 0), $realNoShow),
            'blocked_at' => $this->blocked_at,
            'barberShop' => $this->whenLoaded('barberShop'),
        ];
    }
}
