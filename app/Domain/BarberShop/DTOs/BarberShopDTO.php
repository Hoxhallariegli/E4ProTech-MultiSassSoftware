<?php

namespace App\Domain\BarberShop\DTOs;

class BarberShopDTO
{
    public function __construct(
        public readonly ?string $owner_id,
        public readonly string $name,
        public readonly string $app_name,
        public readonly string $slug,
        public readonly ?string $logo,
        public readonly ?string $banner,
        public readonly string $primary_color,
        public readonly string $secondary_color,
        public readonly ?string $trial_ends_at,
        public readonly ?string $expires_at,
        public readonly bool $active,
        public readonly bool $sms_enabled,
        public readonly string $timezone,
        public readonly ?int $max_no_show_before_block,
        public readonly ?int $min_service_time,
        public readonly ?string $business_type,
        public readonly ?string $staff_label,
        public readonly ?string $staff_label_plural,
        public readonly ?string $shop_label,
        public readonly ?string $service_label,
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            owner_id: isset($data['owner_id']) && $data['owner_id'] !== '' ? (string) $data['owner_id'] : null,
            name: (string) ($data['name'] ?? ''),
            app_name: (string) ($data['app_name'] ?? ''),
            slug: (string) ($data['slug'] ?? ''),
            logo: (string) ($data['logo'] ?? ''),
            banner: (string) ($data['banner'] ?? ''),
            primary_color: (string) ($data['primary_color'] ?? ''),
            secondary_color: (string) ($data['secondary_color'] ?? ''),
            trial_ends_at: $data['trial_ends_at'] ?? null,
            expires_at: $data['expires_at'] ?? null,
            active: (bool) ($data['active'] ?? false),
            sms_enabled: (bool) ($data['sms_enabled'] ?? false),
            timezone: (string) ($data['timezone'] ?? ''),
            max_no_show_before_block: isset($data['max_no_show_before_block']) && $data['max_no_show_before_block'] !== '' ? (int) $data['max_no_show_before_block'] : null,
            min_service_time: isset($data['min_service_time']) && $data['min_service_time'] !== '' ? (int) $data['min_service_time'] : null,
            business_type: isset($data['business_type']) && $data['business_type'] !== '' ? (string) $data['business_type'] : 'barbershop',
            staff_label: isset($data['staff_label']) ? (string) $data['staff_label'] : null,
            staff_label_plural: isset($data['staff_label_plural']) ? (string) $data['staff_label_plural'] : null,
            shop_label: isset($data['shop_label']) ? (string) $data['shop_label'] : null,
            service_label: isset($data['service_label']) ? (string) $data['service_label'] : null,
        );
    }

    public function toArray(): array {
        return [
            'owner_id' => $this->owner_id,
            'name' => $this->name,
            'app_name' => $this->app_name,
            'slug' => $this->slug,
            'logo' => $this->logo,
            'banner' => $this->banner,
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'trial_ends_at' => $this->trial_ends_at,
            'expires_at' => $this->expires_at,
            'active' => $this->active,
            'sms_enabled' => $this->sms_enabled,
            'timezone' => $this->timezone,
            'max_no_show_before_block' => $this->max_no_show_before_block,
            'min_service_time' => $this->min_service_time,
            'business_type' => $this->business_type,
            'staff_label' => $this->staff_label,
            'staff_label_plural' => $this->staff_label_plural,
            'shop_label' => $this->shop_label,
            'service_label' => $this->service_label,
        ];
    }
}
