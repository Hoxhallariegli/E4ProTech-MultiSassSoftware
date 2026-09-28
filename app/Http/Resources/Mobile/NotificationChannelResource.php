<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationChannelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'barber_shop_id' => $this->barber_shop_id,
            'channel' => $this->channel,
            'enabled' => $this->enabled,
            'daily_limit' => $this->daily_limit,
            'barberShop' => $this->whenLoaded('barberShop'),
        ];
    }
}