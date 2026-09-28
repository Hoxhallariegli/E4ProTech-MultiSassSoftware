<?php

namespace App\Domain\Review\DTOs;

class ReviewDTO
{
    public function __construct(
        public readonly ?int $barber_shop_id,
        public readonly ?int $barber_id,
        public readonly ?int $customer_id,
        public readonly ?int $booking_id,
        public readonly ?int $rating,
        public readonly ?string $comment,
    ) {}
    public static function fromArray(array $data): self { return new self(
            barber_shop_id: isset($data['barber_shop_id']) && $data['barber_shop_id'] !== '' ? (int) $data['barber_shop_id'] : null,
            barber_id: isset($data['barber_id']) && $data['barber_id'] !== '' ? (int) $data['barber_id'] : null,
            customer_id: isset($data['customer_id']) && $data['customer_id'] !== '' ? (int) $data['customer_id'] : null,
            booking_id: isset($data['booking_id']) && $data['booking_id'] !== '' ? (int) $data['booking_id'] : null,
            rating: isset($data['rating']) && $data['rating'] !== '' ? (int) $data['rating'] : null,
            comment: (string) ($data['comment'] ?? ''),
        ); }
    public function toArray(): array { return [
            'barber_shop_id' => $this->barber_shop_id,
            'barber_id' => $this->barber_id,
            'customer_id' => $this->customer_id,
            'booking_id' => $this->booking_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
        ]; }
}