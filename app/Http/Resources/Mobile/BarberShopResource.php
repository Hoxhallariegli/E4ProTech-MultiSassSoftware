<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BarberShopResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'name' => $this->name,
            'app_name' => $this->app_name,
            'slug' => $this->slug,
            'logo' => $this->logo ? asset(ltrim((string) $this->logo, '/')) : null,
            'banner' => $this->banner ? asset(ltrim((string) $this->banner, '/')) : null,
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'trial_ends_at' => $this->trial_ends_at,
            'expires_at' => $this->expires_at,
            'active' => $this->active,
            'sms_enabled' => $this->sms_enabled,
            'timezone' => $this->timezone,
            'max_no_show_before_block' => $this->max_no_show_before_block,
            'min_service_time' => $this->resolved_min_service_time,
            'business_type' => $this->business_type ?? 'barbershop',
            'staff_label' => $this->resolved_staff_label,
            'staff_label_plural' => $this->resolved_staff_label_plural,
            'shop_label' => $this->resolved_shop_label,
            'service_label' => $this->resolved_service_label,
            'owner' => $this->whenLoaded('owner'),
        ];
    }
}
