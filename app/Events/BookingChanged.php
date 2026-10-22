<?php

namespace App\Events;

use App\Models\{Booking};
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BookingChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Booking $item,
        public string $action,
    ) {
        $tenantId = $this->item->barber_shop_id ?? $this->item->id;
        Log::info("📡 [REVERB BROADCAST] Booking #{$this->item->id} ({$this->action}) dispatched for Reverb on channel mobile.{$tenantId}.bookings");
    }

    public function broadcastOn(): array
    {
        $tenantId = $this->item->barber_shop_id ?? $this->item->id;
        return [new PrivateChannel('mobile.' . $tenantId . '.bookings')];
    }

    public function broadcastAs(): string
    {
        return 'bookings.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'data' => $this->action === 'deleted'
                ? ['id' => $this->item->getKey()]
                : $this->item->fresh()->toArray(),
        ];
    }
}
