<?php

namespace App\Domain\NotificationChannel\DTOs;

class NotificationChannelDTO
{
    public function __construct(
        public readonly ?int $barber_shop_id,
        public readonly string $channel,
        public readonly bool $enabled,
        public readonly ?int $daily_limit,
    ) {}
    public static function fromArray(array $data): self { return new self(
            barber_shop_id: isset($data['barber_shop_id']) && $data['barber_shop_id'] !== '' ? (int) $data['barber_shop_id'] : null,
            channel: (string) ($data['channel'] ?? ''),
            enabled: (bool) ($data['enabled'] ?? false),
            daily_limit: isset($data['daily_limit']) && $data['daily_limit'] !== '' ? (int) $data['daily_limit'] : null,
        ); }
    public function toArray(): array { return [
            'barber_shop_id' => $this->barber_shop_id,
            'channel' => $this->channel,
            'enabled' => $this->enabled,
            'daily_limit' => $this->daily_limit,
        ]; }
}