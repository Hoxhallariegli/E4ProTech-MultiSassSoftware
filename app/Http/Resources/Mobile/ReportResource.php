<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'period_type' => $this->period_type,
            'report_date' => $this->report_date,
            'total_bookings' => $this->total_bookings,
            'total_revenue' => $this->total_revenue,
            'total_completed' => $this->total_completed,
            'total_cancelled' => $this->total_cancelled,
            'notes' => $this->notes,
            'barber_id' => $this->barber_id,
            'barber_shop_id' => $this->barber_shop_id,
            'barber' => $this->whenLoaded('barber'),
            'barberShop' => $this->whenLoaded('barberShop'),
        ];
    }
}