<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopFrontPageSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'hero_title' => $this->hero_title,
            'hero_subtitle' => $this->hero_subtitle,
            'hero_button_text' => $this->hero_button_text,
            'services_badge_text' => $this->services_badge_text,
            'services_title' => $this->services_title,
            'staff_badge_text' => $this->staff_badge_text,
            'staff_title' => $this->staff_title,
            'contact_phone' => $this->contact_phone,
            'contact_email' => $this->contact_email,
            'contact_address' => $this->contact_address,
            'google_maps_url' => $this->google_maps_url,
            'footer_text' => $this->footer_text,
            'barber_shop_id' => $this->barber_shop_id,
            'barberShop' => $this->whenLoaded('barberShop'),
        ];
    }
}