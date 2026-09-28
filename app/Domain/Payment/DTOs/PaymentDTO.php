<?php

namespace App\Domain\Payment\DTOs;

class PaymentDTO
{
    public function __construct(
        public readonly ?int $barber_shop_id,
        public readonly ?int $booking_id,
        public readonly float $amount,
        public readonly string $method,
        public readonly string $status,
    ) {}
    public static function fromArray(array $data): self { return new self(
            barber_shop_id: isset($data['barber_shop_id']) && $data['barber_shop_id'] !== '' ? (int) $data['barber_shop_id'] : null,
            booking_id: isset($data['booking_id']) && $data['booking_id'] !== '' ? (int) $data['booking_id'] : null,
            amount: isset($data['amount']) && $data['amount'] !== '' ? (float) $data['amount'] : 0.0,
            method: (string) ($data['method'] ?? ''),
            status: (string) ($data['status'] ?? ''),
        ); }
    public function toArray(): array { return [
            'barber_shop_id' => $this->barber_shop_id,
            'booking_id' => $this->booking_id,
            'amount' => $this->amount,
            'method' => $this->method,
            'status' => $this->status,
        ]; }
}