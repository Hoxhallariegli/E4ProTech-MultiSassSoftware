<?php

namespace App\Domain\MessageLog\DTOs;

class MessageLogDTO
{
    public function __construct(
        public readonly ?int $barber_shop_id,
        public readonly ?int $customer_id,
        public readonly string $channel,
        public readonly string $message,
        public readonly string $status,
        public readonly ?string $sent_at,
    ) {}
    public static function fromArray(array $data): self { return new self(
            barber_shop_id: isset($data['barber_shop_id']) && $data['barber_shop_id'] !== '' ? (int) $data['barber_shop_id'] : null,
            customer_id: isset($data['customer_id']) && $data['customer_id'] !== '' ? (int) $data['customer_id'] : null,
            channel: (string) ($data['channel'] ?? ''),
            message: (string) ($data['message'] ?? ''),
            status: (string) ($data['status'] ?? ''),
            sent_at: $data['sent_at'] ?? null,
        ); }
    public function toArray(): array { return [
            'barber_shop_id' => $this->barber_shop_id,
            'customer_id' => $this->customer_id,
            'channel' => $this->channel,
            'message' => $this->message,
            'status' => $this->status,
            'sent_at' => $this->sent_at,
        ]; }
}