<?php

namespace App\Domain\BulkCancellation\DTOs;

class BulkCancellationDTO
{
    public function __construct(
        public readonly ?int $barber_shop_id,
        public readonly ?int $barber_id,
        public readonly ?string $start_at,
        public readonly ?string $end_at,
        public readonly ?string $reason,
        public readonly ?int $affected_count,
    ) {}
    public static function fromArray(array $data): self { return new self(
            barber_shop_id: isset($data['barber_shop_id']) && $data['barber_shop_id'] !== '' ? (int) $data['barber_shop_id'] : null,
            barber_id: isset($data['barber_id']) && $data['barber_id'] !== '' ? (int) $data['barber_id'] : null,
            start_at: $data['start_at'] ?? null,
            end_at: $data['end_at'] ?? null,
            reason: (string) ($data['reason'] ?? ''),
            affected_count: isset($data['affected_count']) && $data['affected_count'] !== '' ? (int) $data['affected_count'] : null,
        ); }
    public function toArray(): array { return [
            'barber_shop_id' => $this->barber_shop_id,
            'barber_id' => $this->barber_id,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'reason' => $this->reason,
            'affected_count' => $this->affected_count,
        ]; }
}