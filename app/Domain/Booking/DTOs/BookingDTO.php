<?php

namespace App\Domain\Booking\DTOs;

class BookingDTO
{
    public function __construct(
        public readonly ?int $barber_shop_id,
        public readonly ?int $barber_id,
        public readonly ?int $service_id,
        public readonly ?int $customer_id,
        public readonly ?string $appointment_at,
        public readonly string $status,
        public readonly float $total_price,
        public readonly ?string $notes,
        public readonly string $source,
    ) {}
    public static function fromArray(array $data): self { return new self(
            barber_shop_id: isset($data['barber_shop_id']) && $data['barber_shop_id'] !== '' ? (int) $data['barber_shop_id'] : null,
            barber_id: isset($data['barber_id']) && $data['barber_id'] !== '' ? (int) $data['barber_id'] : null,
            service_id: isset($data['service_id']) && $data['service_id'] !== '' ? (int) $data['service_id'] : null,
            customer_id: isset($data['customer_id']) && $data['customer_id'] !== '' ? (int) $data['customer_id'] : null,
            appointment_at: $data['appointment_at'] ?? null,
            status: (string) ($data['status'] ?? ''),
            total_price: isset($data['total_price']) && $data['total_price'] !== '' ? (float) $data['total_price'] : 0.0,
            notes: (string) ($data['notes'] ?? ''),
            source: (string) ($data['source'] ?? ''),
        ); }
    public function toArray(): array { return [
            'barber_shop_id' => $this->barber_shop_id,
            'barber_id' => $this->barber_id,
            'service_id' => $this->service_id,
            'customer_id' => $this->customer_id,
            'appointment_at' => $this->appointment_at,
            'status' => $this->status,
            'total_price' => $this->total_price,
            'notes' => $this->notes,
            'source' => $this->source,
        ]; }
}
