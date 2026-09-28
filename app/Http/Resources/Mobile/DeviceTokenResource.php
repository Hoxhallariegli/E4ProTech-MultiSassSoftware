<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeviceTokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'barber_shop_id' => $this->barber_shop_id,
            'user_id' => $this->user_id,
            'fcm_token' => $this->fcm_token,
            'platform' => $this->platform,
            'last_used_at' => $this->last_used_at,
            'barberShop' => $this->whenLoaded('barberShop'),
            'user' => $this->whenLoaded('user'),
        ];
    }
}