<?php

namespace App\Domain\Subscription\DTOs;

class SubscriptionDTO
{
    public function __construct(
        public readonly ?int $barber_shop_id,
        public readonly ?int $plan_id,
        public readonly ?string $starts_at,
        public readonly ?string $ends_at,
        public readonly string $status,
        public readonly bool $auto_renew,
    ) {}
    public static function fromArray(array $data): self { return new self(
            barber_shop_id: isset($data['barber_shop_id']) && $data['barber_shop_id'] !== '' ? (int) $data['barber_shop_id'] : null,
            plan_id: isset($data['plan_id']) && $data['plan_id'] !== '' ? (int) $data['plan_id'] : null,
            starts_at: $data['starts_at'] ?? null,
            ends_at: $data['ends_at'] ?? null,
            status: (string) ($data['status'] ?? ''),
            auto_renew: (bool) ($data['auto_renew'] ?? false),
        ); }
    public function toArray(): array { return [
            'barber_shop_id' => $this->barber_shop_id,
            'plan_id' => $this->plan_id,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'status' => $this->status,
            'auto_renew' => $this->auto_renew,
        ]; }
}