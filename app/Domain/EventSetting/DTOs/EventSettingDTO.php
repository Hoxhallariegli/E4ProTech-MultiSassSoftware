<?php

namespace App\Domain\EventSetting\DTOs;

class EventSettingDTO
{
    public function __construct(
        public readonly ?int $barber_shop_id,
        public readonly ?int $realtime_event_id,
        public readonly bool $reverb_enabled,
        public readonly bool $firebase_enabled,
    ) {}
    public static function fromArray(array $data): self { return new self(
            barber_shop_id: isset($data['barber_shop_id']) && $data['barber_shop_id'] !== '' ? (int) $data['barber_shop_id'] : null,
            realtime_event_id: isset($data['realtime_event_id']) && $data['realtime_event_id'] !== '' ? (int) $data['realtime_event_id'] : null,
            reverb_enabled: (bool) ($data['reverb_enabled'] ?? false),
            firebase_enabled: (bool) ($data['firebase_enabled'] ?? false),
        ); }
    public function toArray(): array { return [
            'barber_shop_id' => $this->barber_shop_id,
            'realtime_event_id' => $this->realtime_event_id,
            'reverb_enabled' => $this->reverb_enabled,
            'firebase_enabled' => $this->firebase_enabled,
        ]; }
}