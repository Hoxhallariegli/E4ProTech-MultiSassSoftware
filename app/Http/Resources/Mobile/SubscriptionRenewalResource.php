<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionRenewalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'barber_shop_id' => $this->barber_shop_id,
            'plan_id' => $this->plan_id,
            'payment_method' => $this->payment_method,
            'transfer_document' => $this->transfer_document ? asset(ltrim((string) $this->transfer_document, '/')) : null,
            'amount' => $this->amount,
            'notes' => $this->notes,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'barberShop' => $this->whenLoaded('barberShop'),
            'plan' => $this->whenLoaded('plan'),
        ];
    }
}
