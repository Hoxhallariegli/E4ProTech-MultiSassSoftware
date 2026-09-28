<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'barber_shop_id' => $this->barber_shop_id,
            'realtime_event_id' => $this->realtime_event_id,
            'reverb_enabled' => $this->reverb_enabled,
            'firebase_enabled' => $this->firebase_enabled,
            'barberShop' => $this->whenLoaded('barberShop'),
            'realtimeEvent' => $this->whenLoaded('realtimeEvent'),
        ];
    }
}