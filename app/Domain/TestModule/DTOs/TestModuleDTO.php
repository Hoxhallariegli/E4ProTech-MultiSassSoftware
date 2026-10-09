<?php

namespace App\Domain\TestModule\DTOs;

class TestModuleDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly float $qty,
        public readonly float $price,
        public readonly bool $is_active,
        public readonly ?string $due_date,
        public readonly ?string $event_at,
        public readonly ?string $user_id,
        public readonly string $priority,
        public readonly ?string $image,
        public readonly ?string $cover_photo,
    ) {}
    public static function fromArray(array $data): self { return new self(
            name: (string) ($data['name'] ?? ''),
            description: (string) ($data['description'] ?? ''),
            qty: isset($data['qty']) && $data['qty'] !== '' ? (float) $data['qty'] : 0.0,
            price: isset($data['price']) && $data['price'] !== '' ? (float) $data['price'] : 0.0,
            is_active: (bool) ($data['is_active'] ?? false),
            due_date: $data['due_date'] ?? null,
            event_at: $data['event_at'] ?? null,
            user_id: isset($data['user_id']) && $data['user_id'] !== '' ? (string) $data['user_id'] : null,
            priority: (string) ($data['priority'] ?? ''),
            image: (string) ($data['image'] ?? ''),
            cover_photo: (string) ($data['cover_photo'] ?? ''),
        ); }
    public function toArray(): array { return [
            'name' => $this->name,
            'description' => $this->description,
            'qty' => $this->qty,
            'price' => $this->price,
            'is_active' => $this->is_active,
            'due_date' => $this->due_date,
            'event_at' => $this->event_at,
            'user_id' => $this->user_id,
            'priority' => $this->priority,
            'image' => $this->image,
            'cover_photo' => $this->cover_photo,
        ]; }
}