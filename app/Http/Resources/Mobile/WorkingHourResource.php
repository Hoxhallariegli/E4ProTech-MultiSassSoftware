<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkingHourResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'barber_id' => $this->barber_id,
            'day_of_week' => $this->day_of_week,
            'open_time' => $this->open_time,
            'close_time' => $this->close_time,
            'lunch_start' => $this->lunch_start,
            'lunch_end' => $this->lunch_end,
            'is_closed' => $this->is_closed,
            'barber' => $this->whenLoaded('barber'),
        ];
    }
}
