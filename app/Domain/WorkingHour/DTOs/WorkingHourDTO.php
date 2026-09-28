<?php

namespace App\Domain\WorkingHour\DTOs;

class WorkingHourDTO
{
    public function __construct(
        public readonly ?int $barber_id,
        public readonly string $day_of_week,
        public readonly ?string $open_time,
        public readonly ?string $close_time,
        public readonly ?string $lunch_start,
        public readonly ?string $lunch_end,
        public readonly bool $is_closed,
    ) {}
    public static function fromArray(array $data): self { return new self(
            barber_id: isset($data['barber_id']) && $data['barber_id'] !== '' ? (int) $data['barber_id'] : null,
            day_of_week: (string) ($data['day_of_week'] ?? ''),
            open_time: $data['open_time'] ?? null,
            close_time: $data['close_time'] ?? null,
            lunch_start: $data['lunch_start'] ?? null,
            lunch_end: $data['lunch_end'] ?? null,
            is_closed: (bool) ($data['is_closed'] ?? false),
        ); }
    public function toArray(): array { return [
            'barber_id' => $this->barber_id,
            'day_of_week' => $this->day_of_week,
            'open_time' => $this->open_time,
            'close_time' => $this->close_time,
            'lunch_start' => $this->lunch_start,
            'lunch_end' => $this->lunch_end,
            'is_closed' => $this->is_closed,
        ]; }
}
