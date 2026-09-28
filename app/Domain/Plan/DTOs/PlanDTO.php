<?php

namespace App\Domain\Plan\DTOs;

class PlanDTO
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly ?int $duration_months,
        public readonly ?int $max_barbers,
        public readonly ?int $max_services,
        public readonly ?int $max_shops,
        public readonly bool $active,
    ) {}
    public static function fromArray(array $data): self { return new self(
            name: (string) ($data['name'] ?? ''),
            price: isset($data['price']) && $data['price'] !== '' ? (float) $data['price'] : 0.0,
            duration_months: isset($data['duration_months']) && $data['duration_months'] !== '' ? (int) $data['duration_months'] : null,
            max_barbers: isset($data['max_barbers']) && $data['max_barbers'] !== '' ? (int) $data['max_barbers'] : null,
            max_services: isset($data['max_services']) && $data['max_services'] !== '' ? (int) $data['max_services'] : null,
            max_shops: isset($data['max_shops']) && $data['max_shops'] !== '' ? (int) $data['max_shops'] : null,
            active: (bool) ($data['active'] ?? false),
        ); }
    public function toArray(): array { return [
            'name' => $this->name,
            'price' => $this->price,
            'duration_months' => $this->duration_months,
            'max_barbers' => $this->max_barbers,
            'max_services' => $this->max_services,
            'max_shops' => $this->max_shops,
            'active' => $this->active,
        ]; }
}