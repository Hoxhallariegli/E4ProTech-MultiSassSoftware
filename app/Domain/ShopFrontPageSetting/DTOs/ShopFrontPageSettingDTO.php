<?php

namespace App\Domain\ShopFrontPageSetting\DTOs;

class ShopFrontPageSettingDTO
{
    public function __construct(
        public readonly ?string $hero_title,
        public readonly ?string $hero_subtitle,
        public readonly ?string $hero_button_text,
        public readonly ?string $services_badge_text,
        public readonly ?string $services_title,
        public readonly ?string $staff_badge_text,
        public readonly ?string $staff_title,
        public readonly ?string $contact_phone,
        public readonly ?string $contact_email,
        public readonly ?string $contact_address,
        public readonly ?string $google_maps_url,
        public readonly ?string $footer_text,
        public readonly ?int $barber_shop_id,
    ) {}
    public static function fromArray(array $data): self { return new self(
            hero_title: (string) ($data['hero_title'] ?? ''),
            hero_subtitle: (string) ($data['hero_subtitle'] ?? ''),
            hero_button_text: (string) ($data['hero_button_text'] ?? ''),
            services_badge_text: (string) ($data['services_badge_text'] ?? ''),
            services_title: (string) ($data['services_title'] ?? ''),
            staff_badge_text: (string) ($data['staff_badge_text'] ?? ''),
            staff_title: (string) ($data['staff_title'] ?? ''),
            contact_phone: (string) ($data['contact_phone'] ?? ''),
            contact_email: (string) ($data['contact_email'] ?? ''),
            contact_address: (string) ($data['contact_address'] ?? ''),
            google_maps_url: (string) ($data['google_maps_url'] ?? ''),
            footer_text: (string) ($data['footer_text'] ?? ''),
            barber_shop_id: isset($data['barber_shop_id']) && $data['barber_shop_id'] !== '' ? (int) $data['barber_shop_id'] : null,
        ); }
    public function toArray(): array { return [
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
        ]; }
}