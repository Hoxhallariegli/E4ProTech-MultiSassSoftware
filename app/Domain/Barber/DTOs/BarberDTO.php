<?php

namespace App\Domain\Barber\DTOs;

class BarberDTO
{
    public function __construct(
        public readonly ?int $barber_shop_id,
        public readonly ?string $user_id,
        public readonly string $name,
        public readonly string $phone,
        public readonly ?string $photo,
        public readonly ?string $bio,
        public readonly bool $active,
    ) {}
    public static function fromArray(array $data): self { return new self(
            barber_shop_id: isset($data['barber_shop_id']) && $data['barber_shop_id'] !== '' ? (int) $data['barber_shop_id'] : null,
            user_id: isset($data['user_id']) && $data['user_id'] !== '' ? (string) $data['user_id'] : null,
            name: (string) ($data['name'] ?? ''),
            phone: (string) ($data['phone'] ?? ''),
            photo: (string) ($data['photo'] ?? ''),
            bio: (string) ($data['bio'] ?? ''),
            active: (bool) ($data['active'] ?? false),
        ); }
    public function toArray(): array { return [
            'barber_shop_id' => $this->barber_shop_id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'photo' => $this->photo,
            'bio' => $this->bio,
            'active' => $this->active,
        ]; }
}