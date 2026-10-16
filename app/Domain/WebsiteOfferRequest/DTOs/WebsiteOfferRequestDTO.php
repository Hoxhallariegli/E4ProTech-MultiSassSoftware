<?php

namespace App\Domain\WebsiteOfferRequest\DTOs;

class WebsiteOfferRequestDTO
{
    public function __construct(
        public readonly ?int $barber_shop_id,
        public readonly ?string $user_id,
        public readonly ?string $desired_domain,
        public readonly ?string $phone,
        public readonly ?string $notes,
        public readonly string $status,
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            barber_shop_id: isset($data['barber_shop_id']) && $data['barber_shop_id'] !== '' ? (int) $data['barber_shop_id'] : null,
            user_id: isset($data['user_id']) && $data['user_id'] !== '' ? (string) $data['user_id'] : null,
            desired_domain: (string) ($data['desired_domain'] ?? ''),
            phone: (string) ($data['phone'] ?? ''),
            notes: (string) ($data['notes'] ?? ''),
            status: (string) ($data['status'] ?? 'pending'),
        );
    }

    public function toArray(): array {
        return [
            'barber_shop_id' => $this->barber_shop_id,
            'user_id' => $this->user_id,
            'desired_domain' => $this->desired_domain,
            'phone' => $this->phone,
            'notes' => $this->notes,
            'status' => $this->status,
        ];
    }
}
