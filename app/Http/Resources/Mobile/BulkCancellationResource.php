<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BulkCancellationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'barber_shop_id' => $this->barber_shop_id,
            'barber_id' => $this->barber_id,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'reason' => $this->reason,
            'affected_count' => $this->affected_count,
            'barberShop' => $this->whenLoaded('barberShop'),
            'barber' => $this->whenLoaded('barber'),
        ];
    }
}