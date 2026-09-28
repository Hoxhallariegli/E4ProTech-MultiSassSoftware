<?php

namespace App\Domain\MessageTemplate\DTOs;

class MessageTemplateDTO
{
    public function __construct(
        public readonly ?int $barber_shop_id,
        public readonly string $channel,
        public readonly string $type,
        public readonly string $content,
    ) {}
    public static function fromArray(array $data): self { return new self(
            barber_shop_id: isset($data['barber_shop_id']) && $data['barber_shop_id'] !== '' ? (int) $data['barber_shop_id'] : null,
            channel: (string) ($data['channel'] ?? ''),
            type: (string) ($data['type'] ?? ''),
            content: (string) ($data['content'] ?? ''),
        ); }
    public function toArray(): array { return [
            'barber_shop_id' => $this->barber_shop_id,
            'channel' => $this->channel,
            'type' => $this->type,
            'content' => $this->content,
        ]; }
}