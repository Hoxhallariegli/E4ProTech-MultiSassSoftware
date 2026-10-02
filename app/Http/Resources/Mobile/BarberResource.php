<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BarberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'barber_shop_id' => $this->barber_shop_id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'photo' => $this->photo ? asset(ltrim((string) $this->photo, '/')) : null,
            'bio' => $this->bio,
            'active' => $this->active,
            'user_name' => $this->user?->name,
            'barberShop' => $this->whenLoaded('barberShop'),
            'user' => $this->whenLoaded('user'),
        ];
    }
}
