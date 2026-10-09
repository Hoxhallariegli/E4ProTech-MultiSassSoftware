<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestModuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'qty' => $this->qty,
            'price' => $this->price,
            'is_active' => $this->is_active,
            'due_date' => $this->due_date,
            'event_at' => $this->event_at,
            'user_id' => $this->user_id,
            'priority' => $this->priority,
            'image' => $this->image ? asset(ltrim((string) $this->image, '/')) : null,
            'cover_photo' => $this->cover_photo ? asset(ltrim((string) $this->cover_photo, '/')) : null,
            'user' => $this->whenLoaded('user'),
        ];
    }
}