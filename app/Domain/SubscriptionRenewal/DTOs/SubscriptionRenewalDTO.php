<?php

namespace App\Domain\SubscriptionRenewal\DTOs;

class SubscriptionRenewalDTO
{
    public function __construct(
        public readonly ?int $barber_shop_id,
        public readonly ?int $plan_id,
        public readonly string $payment_method,
        public readonly ?string $transfer_document,
        public readonly float $amount,
        public readonly ?string $notes,
        public readonly string $status,
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            barber_shop_id: isset($data['barber_shop_id']) && $data['barber_shop_id'] !== '' ? (int) $data['barber_shop_id'] : null,
            plan_id: isset($data['plan_id']) && $data['plan_id'] !== '' ? (int) $data['plan_id'] : null,
            payment_method: (string) ($data['payment_method'] ?? 'bank_transfer'),
            transfer_document: (string) ($data['transfer_document'] ?? ''),
            amount: isset($data['amount']) && $data['amount'] !== '' ? (float) $data['amount'] : 0.0,
            notes: (string) ($data['notes'] ?? ''),
            status: (string) ($data['status'] ?? 'pending'),
        );
    }

    public function toArray(): array {
        return [
            'barber_shop_id' => $this->barber_shop_id,
            'plan_id' => $this->plan_id,
            'payment_method' => $this->payment_method,
            'transfer_document' => $this->transfer_document,
            'amount' => $this->amount,
            'notes' => $this->notes,
            'status' => $this->status,
        ];
    }
}
