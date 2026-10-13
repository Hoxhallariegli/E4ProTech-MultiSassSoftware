<?php

namespace App\Domain\Report\DTOs;

class ReportDTO
{
    public function __construct(
        public readonly string $period_type,
        public readonly ?string $report_date,
        public readonly ?int $total_bookings,
        public readonly float $total_revenue,
        public readonly ?int $total_completed,
        public readonly ?int $total_cancelled,
        public readonly ?string $notes,
        public readonly ?int $barber_id,
        public readonly ?int $barber_shop_id,
    ) {}
    public static function fromArray(array $data): self { return new self(
            period_type: (string) ($data['period_type'] ?? ''),
            report_date: $data['report_date'] ?? null,
            total_bookings: isset($data['total_bookings']) && $data['total_bookings'] !== '' ? (int) $data['total_bookings'] : null,
            total_revenue: isset($data['total_revenue']) && $data['total_revenue'] !== '' ? (float) $data['total_revenue'] : 0.0,
            total_completed: isset($data['total_completed']) && $data['total_completed'] !== '' ? (int) $data['total_completed'] : null,
            total_cancelled: isset($data['total_cancelled']) && $data['total_cancelled'] !== '' ? (int) $data['total_cancelled'] : null,
            notes: (string) ($data['notes'] ?? ''),
            barber_id: isset($data['barber_id']) && $data['barber_id'] !== '' ? (int) $data['barber_id'] : null,
            barber_shop_id: isset($data['barber_shop_id']) && $data['barber_shop_id'] !== '' ? (int) $data['barber_shop_id'] : null,
        ); }
    public function toArray(): array { return [
            'period_type' => $this->period_type,
            'report_date' => $this->report_date,
            'total_bookings' => $this->total_bookings,
            'total_revenue' => $this->total_revenue,
            'total_completed' => $this->total_completed,
            'total_cancelled' => $this->total_cancelled,
            'notes' => $this->notes,
            'barber_id' => $this->barber_id,
            'barber_shop_id' => $this->barber_shop_id,
        ]; }
}