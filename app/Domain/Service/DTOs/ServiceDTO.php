<?php

namespace App\Domain\Service\DTOs;

class ServiceDTO
{
    public function __construct(
        public readonly ?int $barber_shop_id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly float $price,
        public readonly ?int $duration_minutes,
        public readonly ?string $category,
        public readonly bool $active,
    ) {}
    public static function fromArray(array $data): self { return new self(
            barber_shop_id: isset($data['barber_shop_id']) && $data['barber_shop_id'] !== '' ? (int) $data['barber_shop_id'] : null,
            name: (string) ($data['name'] ?? ''),
            description: (string) ($data['description'] ?? ''),
            price: isset($data['price']) && $data['price'] !== '' ? (float) $data['price'] : 0.0,
            duration_minutes: isset($data['duration_minutes']) && $data['duration_minutes'] !== '' ? (int) $data['duration_minutes'] : null,
            category: (string) ($data['category'] ?? ''),
            active: (bool) ($data['active'] ?? false),
        ); }
    public function toArray(): array { return [
            'barber_shop_id' => $this->barber_shop_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'duration_minutes' => $this->duration_minutes,
            'category' => $this->category,
            'active' => $this->active,
        ]; }
}