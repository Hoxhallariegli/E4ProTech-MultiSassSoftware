<?php

namespace App\Domain\MessageQueue\DTOs;

class MessageQueueDTO
{
    public function __construct(
        public readonly ?int $barber_shop_id,
        public readonly ?int $booking_id,
        public readonly string $channel,
        public readonly string $phone_number,
        public readonly string $message_content,
        public readonly ?string $scheduled_at,
        public readonly string $status,
        public readonly ?int $retry_count,
    ) {}
    public static function fromArray(array $data): self { return new self(
            barber_shop_id: isset($data['barber_shop_id']) && $data['barber_shop_id'] !== '' ? (int) $data['barber_shop_id'] : null,
            booking_id: isset($data['booking_id']) && $data['booking_id'] !== '' ? (int) $data['booking_id'] : null,
            channel: (string) ($data['channel'] ?? ''),
            phone_number: (string) ($data['phone_number'] ?? ''),
            message_content: (string) ($data['message_content'] ?? ''),
            scheduled_at: $data['scheduled_at'] ?? null,
            status: (string) ($data['status'] ?? ''),
            retry_count: isset($data['retry_count']) && $data['retry_count'] !== '' ? (int) $data['retry_count'] : null,
        ); }
    public function toArray(): array { return [
            'barber_shop_id' => $this->barber_shop_id,
            'booking_id' => $this->booking_id,
            'channel' => $this->channel,
            'phone_number' => $this->phone_number,
            'message_content' => $this->message_content,
            'scheduled_at' => $this->scheduled_at,
            'status' => $this->status,
            'retry_count' => $this->retry_count,
        ]; }
}