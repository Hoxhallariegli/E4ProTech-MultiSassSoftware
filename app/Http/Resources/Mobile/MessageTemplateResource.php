<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'barber_shop_id' => $this->barber_shop_id,
            'channel' => $this->channel,
            'type' => $this->type,
            'content' => $this->content,
            'barberShop' => $this->whenLoaded('barberShop'),
        ];
    }
}