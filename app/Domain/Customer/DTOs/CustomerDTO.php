<?php

namespace App\Domain\Customer\DTOs;

class CustomerDTO
{
    public function __construct(
        public readonly ?int $barber_shop_id,
        public readonly string $name,
        public readonly string $phone,
        public readonly ?string $email,
        public readonly ?string $photo,
        public readonly ?int $total_bookings,
        public readonly ?int $no_show_count,
        public readonly ?string $blocked_at,
    ) {}
    public static function fromArray(array $data): self { return new self(
            barber_shop_id: isset($data['barber_shop_id']) && $data['barber_shop_id'] !== '' ? (int) $data['barber_shop_id'] : null,
            name: (string) ($data['name'] ?? ''),
            phone: (string) ($data['phone'] ?? ''),
            email: (string) ($data['email'] ?? ''),
            photo: (string) ($data['photo'] ?? ''),
            total_bookings: isset($data['total_bookings']) && $data['total_bookings'] !== '' ? (int) $data['total_bookings'] : null,
            no_show_count: isset($data['no_show_count']) && $data['no_show_count'] !== '' ? (int) $data['no_show_count'] : null,
            blocked_at: $data['blocked_at'] ?? null,
        ); }
    public function toArray(): array { return [
            'barber_shop_id' => $this->barber_shop_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'photo' => $this->photo,
            'total_bookings' => $this->total_bookings,
            'no_show_count' => $this->no_show_count,
            'blocked_at' => $this->blocked_at,
        ]; }
}