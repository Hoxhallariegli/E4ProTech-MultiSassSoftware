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
            'open_time' => $this->open_time ? substr((string)$this->open_time, 0, 5) : null,
            'close_time' => $this->close_time ? substr((string)$this->close_time, 0, 5) : null,
            'lunch_start' => $this->lunch_start ? substr((string)$this->lunch_start, 0, 5) : null,
            'lunch_end' => $this->lunch_end ? substr((string)$this->lunch_end, 0, 5) : null,
            'is_closed' => (bool)$this->is_closed,
            'barber' => $this->whenLoaded('barber'),
        ];
    }
}
