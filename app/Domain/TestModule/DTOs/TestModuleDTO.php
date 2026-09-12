<?php

namespace App\Domain\TestModule\DTOs;

class TestModuleDTO
{
    public function __construct(
        public readonly mixed $name,
        public readonly mixed $description,
        public readonly mixed $qty,
        public readonly mixed $price,
        public readonly mixed $is_active,
        public readonly mixed $due_date,
        public readonly mixed $event_at,
        public readonly mixed $user_id,
        public readonly mixed $priority,
        public readonly mixed $image,
        public readonly mixed $cover_photo,
    ) {}
    public static function fromArray(array $data): self { return new self(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            qty: $data['qty'] ?? null,
            price: $data['price'] ?? null,
            is_active: $data['is_active'] ?? null,
            due_date: $data['due_date'] ?? null,
            event_at: $data['event_at'] ?? null,
            user_id: $data['user_id'] ?? null,
            priority: $data['priority'] ?? null,
            image: $data['image'] ?? null,
            cover_photo: $data['cover_photo'] ?? null,
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