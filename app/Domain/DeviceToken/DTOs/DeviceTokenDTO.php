<?php

namespace App\Domain\DeviceToken\DTOs;

class DeviceTokenDTO
{
    public function __construct(
        public readonly ?int $barber_shop_id,
        public readonly ?string $user_id,
        public readonly string $fcm_token,
        public readonly string $platform,
        public readonly ?string $last_used_at,
    ) {}
    public static function fromArray(array $data): self { return new self(
            barber_shop_id: isset($data['barber_shop_id']) && $data['barber_shop_id'] !== '' ? (int) $data['barber_shop_id'] : null,
            user_id: isset($data['user_id']) && $data['user_id'] !== '' ? (string) $data['user_id'] : null,
            fcm_token: (string) ($data['fcm_token'] ?? ''),
            platform: (string) ($data['platform'] ?? ''),
            last_used_at: $data['last_used_at'] ?? null,
        ); }
    public function toArray(): array { return [
            'barber_shop_id' => $this->barber_shop_id,
            'user_id' => $this->user_id,
            'fcm_token' => $this->fcm_token,
            'platform' => $this->platform,
            'last_used_at' => $this->last_used_at,
        ]; }
}