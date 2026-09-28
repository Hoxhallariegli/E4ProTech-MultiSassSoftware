<?php

namespace App\Events;

use App\Models\{BarberShop};
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BarberShopChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public BarberShop $item,
        public string $action,
    ) {}

    public function broadcastOn(): array
    {
        $tenantId = $this->item->barber_shop_id ?? $this->item->id;
        return [new PrivateChannel('mobile.' . $tenantId . '.barber-shops')];
    }

    public function broadcastAs(): string
    {
        return 'barber-shops.changed';
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